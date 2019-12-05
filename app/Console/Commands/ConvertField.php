<?php namespace App\Console\Commands;

use App\Form;
use App\Http\Controllers\FormController;
use App\KoraFields\GalleryField;
use App\KoraFields\ModelField;
use App\KoraFields\PlaylistField;
use App\KoraFields\VideoField;
use App\Record;
use Illuminate\Console\Command;

class ConvertField extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kora:convert-field {fid} {flid} {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts a field and its record data, into a new field type';
    
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
        $this->info('Beginning field conversion...');

        $fid = $this->argument('fid');
        $form = FormController::getForm($fid);
        $flid = $this->argument('flid');
        $layout = $form->layout;
        $field = $layout['fields'][$flid];
        $oldOptions = $field; //Copy this over before we start modifying

        $oldType = $field['type'];
        $newType = $this->argument('type');

        $status = '';
        if($this->confirm('Changing field types may alter data unintentionally. Do you wish to continue?')) {
            $field['type'] = $newType;
            $field['default'] = null; //Default always goes away? TODO::CONVERT

            $crt = new \CreateRecordsTable(); //Need this for table modifications
            $recModel = new Record(array(),$fid);
            $tmpName = uniqid();

            switch($oldType) { //TODO::NEWFIELD
                case Form::_TEXT: //TODO::CONVERT
//                Form::_BOOLEAN,
//                Form::_RICH_TEXT,
//                Form::_INTEGER,
//                Form::_FLOAT,
//                Form::_LIST,
//                Form::_MULTI_SELECT_LIST,
//                Form::_GENERATED_LIST,
//                Form::_DATE,
//                Form::_DATETIME,
//                Form::_HISTORICAL_DATE,
//                Form::_GEOLOCATOR,
//                Form::_ASSOCIATOR,
                    break;
                case Form::_RICH_TEXT:
                    if($newType == Form::_TEXT) {
                        $field['options'] = ['Regex' => '', 'MultiLine' => 0];

                        $crt->addTextColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $rec->{$tmpName} = strip_tags($rec->{$flid});
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_INTEGER:
                    if($newType == Form::_FLOAT) {
                        if($field['options']['Max'] != '')
                            $field['options']['Max'] = (double)$field['options']['Max'];
                        if($field['options']['Min'] != '')
                            $field['options']['Min'] = (double)$field['options']['Min'];

                        $crt->addDoubleColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $rec->{$tmpName} = (double)$rec->{$flid};
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_FLOAT:
                    if($newType == Form::_INTEGER) {
                        if($field['options']['Max'] != '')
                            $field['options']['Max'] = intval(ceil($field['options']['Max']));
                        if($field['options']['Min'] != '')
                            $field['options']['Min'] = intval(floor($field['options']['Min']));

                        $crt->addIntegerColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $rec->{$tmpName} = intval(round($rec->{$flid}));
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_LIST:
                    if($newType == Form::_MULTI_SELECT_LIST) {
                        //No Field Options Modifications

                        $crt->addJSONColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $rec->{$tmpName} = json_encode([$rec->{$flid}]);
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else if($newType == Form::_GENERATED_LIST) {
                        $field['options'] = ['Regex' => '', 'Options' => []];

                        $crt->addJSONColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $rec->{$tmpName} = json_encode([$rec->{$flid}]);
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_MULTI_SELECT_LIST:
                    if($newType == Form::_LIST) {
                        $crt->addEnumColumn($fid, $tmpName, $field['options']['Options']);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $rec->{$tmpName} = json_encode($rec->{$flid})[0];
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else if($newType == Form::_GENERATED_LIST) {
                        $field['options'] = ['Regex' => '', 'Options' => []];

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_GENERATED_LIST:
                    $newOpts = []; //Since Gen List doesn't keep track of option set, but other lists do, we capture and populate them
                    if($newType == Form::_LIST) {
                        $field['options'] = ['Options' => []];
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $newOpts[] = json_encode($rec->{$flid});
                        }
                        $field['options']['Options'] = array_unique($newOpts);

                        $crt->addEnumColumn($fid, $tmpName, $field['options']['Options']);
                        foreach($records as $rec) {
                            $rec->{$tmpName} = json_encode($rec->{$flid})[0];
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else if($newType == Form::_MULTI_SELECT_LIST) {
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $newOpts[] = json_encode($rec->{$flid});
                        }
                        $field['options']['Options'] = array_unique($newOpts);

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_DATE:
                    if($newType == Form::_DATETIME) {
                        //No Field Options Modifications

                        $crt->addDateTimeColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $rec->{$tmpName} = $rec->{$flid}.' 00:00:00';
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else if($newType == Form::_HISTORICAL_DATE) {
                        $field['options']['ShowPrefix'] = 0;
                        $field['options']['ShowEra'] = 0;

                        $crt->addJSONColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $dateInfo = explode('-',$rec->{$flid});
                            $date = ['month' => $dateInfo[1], 'day' => $dateInfo[2], 'year' => $dateInfo[0],
                                'prefix' => '','era' => 'CE'];
                            $rec->{$tmpName} = json_encode($date);
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_DATETIME:
                    if($newType == Form::_DATE) {
                        //No Field Options Modifications

                        $crt->addDateColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $parts = explode(' ', $rec->{$flid});
                            $rec->{$tmpName} = $parts[0];
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else if($newType == Form::_HISTORICAL_DATE) {
                        $field['options']['ShowPrefix'] = 0;
                        $field['options']['ShowEra'] = 0;

                        $crt->addJSONColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $parts = explode(' ', $rec->{$flid});
                            $dateInfo = explode('-',$parts[0]);
                            $date = ['month' => $dateInfo[1], 'day' => $dateInfo[2], 'year' => $dateInfo[0],
                                'prefix' => '','era' => 'CE'];
                            $rec->{$tmpName} = json_encode($date);
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_HISTORICAL_DATE:
                    if($newType == Form::_DATE) {
                        unset($field['options']['ShowPrefix']);
                        unset($field['options']['ShowEra']);

                        //TODO::DATABASE
                    } else if($newType == Form::_DATETIME) {
                        unset($field['options']['ShowPrefix']);
                        unset($field['options']['ShowEra']);

                        //TODO::DATABASE
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_DOCUMENTS:
                    if($newType == Form::_GALLERY) {
                        $field['options']['FileTypes'] = GalleryField::SUPPORTED_TYPES;

                        //No Database Record Modifications
                    } else if($newType == Form::_PLAYLIST) {
                        $field['options']['FileTypes'] = PlaylistField::SUPPORTED_TYPES;

                        //No Database Record Modifications
                    } else if($newType == Form::_VIDEO) {
                        $field['options']['FileTypes'] = VideoField::SUPPORTED_TYPES;

                        //No Database Record Modifications
                    } else if($newType == Form::_3D_MODEL) {
                        $field['options']['FileTypes'] = ModelField::SUPPORTED_TYPES;
                        $field['options']['ModelColor'] = '#ddd';
                        $field['options']['BackColorOne'] = '#2E4F5E';
                        $field['options']['BackColorTwo'] = '#152730';

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_GALLERY:
                    if($newType == Form::_DOCUMENTS) {
                        //No Field Options Modifications

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_PLAYLIST:
                    if($newType == Form::_DOCUMENTS) {
                        //No Field Options Modifications

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_VIDEO:
                    if($newType == Form::_DOCUMENTS) {
                        //No Field Options Modifications

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_3D_MODEL:
                    if($newType == Form::_DOCUMENTS) {
                        unset($field['options']['ModelColor']);
                        unset($field['options']['BackColorOne']);
                        unset($field['options']['BackColorTwo']);

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                default:
                    $status = 'bad_original_type';
                    break;
            }

            switch($status) {
                case 'bad_original_type':
                    $this->error("This field type cannot be converted!");
                    break;
                case 'bad_requested_type':
                    $this->error("Cannot convert this field to the requested type!");
                    break;
                default:
                    $layout['fields'][$flid] = $field;
                    $form->layout = $layout;
                    $form->save();
                    $this->info('Old field options: '.json_encode($oldOptions));
                    $this->info('Finished field conversion!');
                    break;
            }
        } else {
            $this->info('Exiting field conversion!');
        }
    }
}
