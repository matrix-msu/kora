<?php namespace App\Console\Commands;

use App\Http\Controllers\FormController;
use App\Record;
use Illuminate\Console\Command;

class GenerateThumbs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora3:generate-thumbs {fid} {flid} {size}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For each file in field, generate thumb files of that size';

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
        $storageType = 'LaravelStorage'; //TODO:: make this a config once we actually support other storage types
        $fid = $this->argument('fid');
        $form = FormController::getForm($fid);
        $flid = $this->argument('flid');

        $thumb = $this->argument('size');
        if(!preg_match("/^[0-9]+[x][0-9]+$/", $thumb)) {
            $this->error('Please use the correct thumbnail format (i.e. "100x100")');
        } else {
            $this->info('Generating thumbnails...');
            $thumbParts = explode('x', $thumb);

            $recTable = new Record(array(), $form->id);
            $records = $recTable->newQuery()->select('id',$flid)->get();

            foreach ($records as $record) {
                $files = json_decode($record->{$flid},true);

                foreach ($files as $file) {
                    //Define the name of the thumb
                    $fileParts = explode('.', $file['name']);
                    $ext = array_pop($fileParts);
                    $thumbFilename = implode('.', $fileParts) . "_$thumb." . $ext;

                    switch ($storageType) {
                        case 'LaravelStorage':
                            $filePath = storage_path('app/files/' . $form->project_id . '/' . $form->id . '/' . $record->id . '/' . $file['name']);
                            $thumbPath = storage_path('app/files/' . $form->project_id . '/' . $form->id . '/' . $record->id . '/' . $thumbFilename);

                            //Check if we already made the thumb
                            if (!file_exists($thumbPath)) {
                                $tImage = new \Imagick($filePath);
                                $tImage->thumbnailImage($thumbParts[0], $thumbParts[1], true);
                                $tImage->writeImage($thumbPath);
                            }
                            break;
                    }
                }
            }

            $this->info('Thumbnails generated!');
            $this->info('PLEASE MAKE SURE FILE PERMISSIONS ARE CORRECT FOR STORAGE');
        }
    }
}
