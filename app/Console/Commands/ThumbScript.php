<?php namespace App\Console\Commands;

use App\Field;
use Illuminate\Console\Command;

class ThumbScript extends Command {

    /*
    |--------------------------------------------------------------------------
    | Thumb Script
    |--------------------------------------------------------------------------
    |
    | 1) This script will regenerate thumbnails for every Gallery field in a
    |    set of forms in Kora3, based on the current sizes listed in each field
    |    options page
    |
    | 2) PLEASE MAKE SURE USER HAS FULL PERMISSIONS TO KORA3 DIRECTORY
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnail:generate {fid*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreates Gallery Fields thumbnails for a Form';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $formIDs = $this->argument('fid');
        $baseDir = config('app.base_path').'storage/app/files/';

        foreach($formIDs as $fid) {
            $galFields = Field::where('fid','=',$fid)->where('type','=','Gallery')->get();
            $fieldCnt = 0;

            foreach($galFields as $gal) {
                $directory = $baseDir.'p'.$gal->pid.'/f'.$gal->fid;
                $flidDir = 'fl'.$gal->flid;
                $small = explode('x',\App\Http\Controllers\FieldController::getFieldOption($gal, "ThumbSmall"));
                $medium = explode('x',\App\Http\Controllers\FieldController::getFieldOption($gal, "ThumbLarge"));

                $fieldCnt += $this->recursiveGenerateThumbnails($directory, $flidDir, $small, $medium);
            }

            $this->info($fieldCnt.' Images Affected in Form '.$fid);
            $this->info('');
        }

        $this->info('PLEASE MAKE SURE FILE PERMISSIONS ARE CORRECT FOR STORAGE');
    }

    /**
     * A recursive function for generating thumbnails.
     *
     * @param  string $directory - Path to files directory
     * @param  string $flidDir - Directory name for field
     * @param  array $small - Size of thumbnail
     * @param  array $medium - Size of medium thumbnail
     *
     * @return int - Number of gallery fields effected
     */
    private function recursiveGenerateThumbnails($directory, $flidDir, $small, $medium) {
        $fcnt = 0;

        foreach(glob("{$directory}/*") as $file) {
            if(is_dir($file)) {
                $parts = explode("/",$file);
                if(end($parts)==$flidDir) {
                    //loop through directory
                    foreach(glob("{$file}/*") as $image) {
                        if(is_file($image)) {
                            $fcnt++;
                            $iParts = explode("/",$image);
                            $imageName = end($iParts);
                            $imagePath = str_replace($imageName, "", $image);

                            //delete old thumbs
                            $deleteThumb = $imagePath.'thumbnail/'.$imageName;
                            $deleteMed = $imagePath.'medium/'.$imageName;
                            if(file_exists($deleteThumb))
                                unlink($deleteThumb);
                            if(file_exists($deleteMed))
                                unlink($deleteMed);

                            //make new thumbs
                            $this->info('Creating thumbs for '.$image);
                            $this->generateThumbs($imagePath, $imageName, $small, $medium);
                        }
                    }
                } else {
                    $fcnt += $this->recursiveGenerateThumbnails($file, $flidDir, $small, $medium);
                }
            }
        }

        return $fcnt;
    }

    /**
     * Generates the actual thumb files.
     *
     * @param  string $imagePath - Path to image file
     * @param  string $imageName - Name of the image
     * @param  array $small - Size of thumbnail
     * @param  array $medium - Size of medium thumbnail
     */
    private function generateThumbs($imagePath, $imageName, $small, $medium) {
        $tImage = new \Imagick($imagePath . $imageName);
        $mImage = new \Imagick($imagePath . $imageName);

        //Size check
        if($small[0]==0 | $small[1]==0) {
            $small[0] = 150;
            $small[1] = 150;
        }
        if($medium[0]==0 | $medium[1]==0) {
            $medium[0] = 300;
            $medium[1] = 300;
        }
        
        //Make directories if they are missing
        if(!file_exists($imagePath.'thumbnail/'))
        	mkdir($imagePath.'thumbnail/',775);
        if(!file_exists($imagePath.'medium/'))
        	mkdir($imagePath.'medium/',775);

        $tImage->thumbnailImage($small[0],$small[1],true);
        $tImage->writeImage($imagePath.'thumbnail/'.$imageName);

        $mImage->thumbnailImage($medium[0],$medium[1],true);
        $mImage->writeImage($imagePath.'medium/'.$imageName);
    }
}
