<?php namespace App\Console\Commands;

use App\Form;
use App\Http\Controllers\FormController;
use App\KoraFields\GalleryField;
use App\KoraFields\HistoricalDateField;
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
        if($this->confirm('Changing field types may result in loss of incompatible data. Do you wish to continue?')) {
            $field['type'] = $newType;

            $crt = new \CreateRecordsTable(); //Need this for table modifications
            $recModel = new Record(array(),$fid);
            $tmpName = "temp_".uniqid();

            switch($oldType) {
                case Form::_TEXT:
                    if($newType == Form::_RICH_TEXT) {
                        $field['options'] = [];

                        $crt->addMediumTextColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            //Rich Text uses different line endings and we want to preserve paragraphs
                            $textWithBreaks = str_replace("\r\n",'<br />', $rec->{$flid});
                            $rec->{$tmpName} = $textWithBreaks;
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else if($newType == Form::_INTEGER) {
                        $field['options'] = ['Min' => '', 'Max' => '', 'Unit' => ''];
                        $field['default'] = '';

                        $crt->addIntegerColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $rec->{$tmpName} = (int)$rec->{$flid};
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_RICH_TEXT:
                    if($newType == Form::_TEXT) {
                        $field['options'] = ['Regex' => '', 'MultiLine' => 1];
                        if(!is_null($field['default']) && $field['default']!='')
                            $field['default'] = strip_tags($field['default']);

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
                        if(!is_null($field['default']) && $field['default']!='')
                            $field['default'] = (double)$field['default'];

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
                        if(!is_null($field['default']) && $field['default']!='')
                            $field['default'] = intval(round($field['default']));

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
                        if(!is_null($field['default']) && $field['default']!='')
                            $field['default'] = [$field['default']];

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
                        if(is_null($field['default']) || $field['default']=='')
                            $newDef = [];
                        else
                            $newDef = [$field['default']];
                        $field['options'] = ['Regex' => '', 'Options' => $newDef];
                        $field['default'] = $newDef;

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
                        if(!is_null($field['default']) && $field['default']!='' && !empty($field['default']))
                            $field['default'] = $field['default'][0];

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
                        if(is_null($field['default']))
                            $field['default'] = [];
                        $field['options'] = ['Regex' => '', 'Options' => $field['default']];

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_GENERATED_LIST:
                    $newOpts = []; //Since Gen List doesn't keep track of option set, but other lists do, we capture and populate them
                    if($newType == Form::_LIST) {
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $newOpts[] = json_encode($rec->{$flid});
                        }
                        $newOpts = array_merge($newOpts,$field['options']);
                        $field['options']['Options'] = array_unique($newOpts);
                        $field['default'] = [];

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
                        $newOpts = array_merge($newOpts,$field['options']);
                        $field['options']['Options'] = array_unique($newOpts);
                        $field['default'] = [];

                        //No Database Record Modifications
                    } else
                        $status = 'bad_requested_type';
                    break;
                case Form::_DATE:
                    if($newType == Form::_DATETIME) {
                        if(!is_null($field['default'])) {
                            $field['default']['hour'] = '';
                            $field['default']['minute'] = '';
                            $field['default']['second'] = '';
                        }

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
                        if(!is_null($field['default'])) {
                            $field['default']['prefix'] = '';
                            $field['default']['era'] = 'CE';
                        }

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
                        if(!is_null($field['default'])) {
                            unset($field['default']['hour']);
                            unset($field['default']['minute']);
                            unset($field['default']['second']);
                        }

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
                        if(!is_null($field['default'])) {
                            unset($field['default']['hour']);
                            unset($field['default']['minute']);
                            unset($field['default']['second']);
                            $field['default']['prefix'] = '';
                            $field['default']['era'] = 'CE';
                        }

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
                        if(!is_null($field['default'])) {
                            unset($field['default']['prefix']);
                            unset($field['default']['era']);
                        }

                        $crt->addDateColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $date = json_decode($rec->{$flid},true);
                            if($date['era']=='CE') {
                                $dateParts = [$date['year'],'01','01'];
                                if($date['month']!='')
                                    $dateParts[1] = $date['month'];
                                if($date['day']!='')
                                    $dateParts[2] = $date['day'];

                                $rec->{$tmpName} = implode('-', $dateParts);
                            } else if($date['era']=='BP' && HistoricalDateField::BEFORE_PRESENT_REFERENCE-$date['year'] > 0) {
                                $dateParts = [$date['year'],'01','01'];
                                if($date['month']!='')
                                    $dateParts[1] = $date['month'];
                                if($date['day']!='')
                                    $dateParts[2] = $date['day'];

                                $rec->{$tmpName} = implode('-', $dateParts);
                            }
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
                    } else if($newType == Form::_DATETIME) {
                        unset($field['options']['ShowPrefix']);
                        unset($field['options']['ShowEra']);
                        if(!is_null($field['default'])) {
                            unset($field['default']['prefix']);
                            unset($field['default']['era']);
                            $field['default']['hour'] = '';
                            $field['default']['minute'] = '';
                            $field['default']['second'] = '';
                        }

                        $crt->addDateTimeColumn($fid, $tmpName);
                        $records = $recModel->newQuery()->whereNotNull($flid)->get();
                        foreach($records as $rec) {
                            $date = json_decode($rec->{$flid},true);
                            if($date['era']=='CE') {
                                $dateParts = [$date['year'],'01','01'];
                                if($date['month']!='')
                                    $dateParts[1] = $date['month'];
                                if($date['day']!='')
                                    $dateParts[2] = $date['day'];

                                $rec->{$tmpName} = implode('-', $dateParts).' 00:00:00';
                            } else if($date['era']=='BP' && HistoricalDateField::BEFORE_PRESENT_REFERENCE-$date['year'] > 0) {
                                $dateParts = [$date['year'],'01','01'];
                                if($date['month']!='')
                                    $dateParts[1] = $date['month'];
                                if($date['day']!='')
                                    $dateParts[2] = $date['day'];

                                $rec->{$tmpName} = implode('-', $dateParts).' 00:00:00';
                            }
                            $rec->save();
                        }
                        $this->info("Preserving old record data at column: $tmpName$flid");
                        $crt->renameColumn($fid,$flid,"$tmpName$flid");
                        $crt->renameColumn($fid,$tmpName,$flid);
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
