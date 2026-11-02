<?php namespace App\Console\Commands;

use App\Http\Controllers\FormController;
use App\KoraFields\FileTypeField;
use App\Record;
use Illuminate\Console\Command;

class ExportMarkdown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:export-markdown {fid} {uid} {title} {longform}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export records as ';

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
        ini_set('memory_limit','2G'); //We might be pulling a lot of rows so this is a safety precaution

        $fid = $this->argument('fid');
        $form = FormController::getForm($fid);

        //Get the data
        $filters = ["revAssoc" => true, "meta" => false, "fields" => 'ALL', "altNames" => false, "assoc" => false,
            "data" => true, "sort" => null, "count" => null, "index" => null];

        $markdownData = $form->getRecordsForExportMarkdown($filters, null, $this->argument('title'), $this->argument('longform'));
        $tmpDir = uniqid();

        foreach($markdownData as $filename => $record) {
            $formattedName = $this->formatValidMarkdownFilename($filename);

            switch(config('filesystems.kora_storage')) {
                case FileTypeField::_LaravelStorage:
                    //Make folder
                    $newPath = storage_path('app/tmpFiles/' . $tmpDir);
                    if(!file_exists($newPath))
                        mkdir($newPath, 0775, true);

                    file_put_contents($newPath . '/' . $formattedName, $record);
                    break;
                case FileTypeField::_JoyentManta:
                    //TODO::MANTA
                    break;
                default:
                    break;
            }
        }

        $this->info("Markdown files exported successfully. Stored at: $newPath\n");
    }

    public function formatValidMarkdownFilename($string, $maxLength = 255) {
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $string);

        // Remove characters not allowed in filenames
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $filename);

        // Remove multiple underscores or dots
        $filename = preg_replace('/[_\.]{2,}/', '_', $filename);

        // Trim to max length
        $filename = substr($filename, 0, $maxLength);

        // Ensure it's not empty
        if (empty($filename)) {
            $filename = 'default_filename';
        }

        return $filename.".md";
    }
}
