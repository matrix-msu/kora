<?php namespace App\Console\Commands;

use App\Http\Controllers\FormController;
use App\Http\Controllers\ImportController;
use Illuminate\Console\Command;

class ImportHelper extends Command {

    /*
    |--------------------------------------------------------------------------
    | Exodus Script
    |--------------------------------------------------------------------------
    |
    | Takes a custom utf8 format file for Kora3 and converts it into an XML for
    | record import. This allows for easy creation of a larger file set without
    | worrying about the nuts and bolts of XML/JSON
    |
    */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:utf8 {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Takes a custom utf8 format file, and converts it into an XML file for import.';

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
        $fileInfo = $this->argument('filePath');
        $parts = explode('/',$fileInfo);
        if(sizeof($parts) > 1) {
            $path = $parts[0] . '/';
            $name = str_replace('.utf8','',$parts[1]);
            $ext = end(explode('.', $fileInfo));
        } else {
            $path = '';
            $name = str_replace('.utf8','',$parts[0]);
            $ext = end(explode('.', $fileInfo));
        }

        if($ext != 'utf8') {
            $this->info("Invalid file extension!");
            return '';
        }

        //Get the XML
        $xml = ImportController::utf8ToXML($fileInfo);

        if($xml == 'invalid_file') {
            $this->info("Could not find the expected UTF8 file!");
        } else {
            $fp = fopen($path.$name.'.xml',"w");
            fwrite($fp,$xml);
            fclose($fp);

            $this->info("Success! File located at $path$name.xml");
        }
    }
}
