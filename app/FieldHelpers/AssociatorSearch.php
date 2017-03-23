<?php
/**
 * Created by PhpStorm.
 * User: fritosxii
 * Date: 11/4/2016
 * Time: 10:12 AM
 */

namespace App\FieldHelpers;


use App\Field;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FormController;
use App\ListField;
use App\Search;
use App\TextField;
use Illuminate\Support\Facades\Session;

class AssociatorSearch
{
        public static function keywordSearch($keyword, $field){
            $activeForms = array();
            $results = array();

            $option = FieldController::getFieldOption($field,'SearchForms');
            if($option!=''){
                $options = explode('[!]',$option);

                foreach($options as $opt){
                    $opt_fid = explode('[fid]',$opt)[1];
                    $opt_search = explode('[search]',$opt)[1];
                    $opt_flids = explode('[flids]',$opt)[1];
                    $opt_flids = explode('-',$opt_flids);

                    if($opt_search == 1)
                        $flids = array();
                        foreach($opt_flids as $flid){
                            $field = FieldController::getField($flid);
                            $flids[$flid] = $field->type;
                        }
                        $activeForms[$opt_fid] = ['flids' => $flids];
                }
            }

            foreach($activeForms as $fid => $details){
                $form = FormController::getForm($fid);

                $rids = AssociatorSearch::search($form->pid, $fid, $keyword, 0);

                foreach($rids as $rid){
                    $kid = $form->pid.'-'.$fid.'-'.$rid;
                    $preview = array();
                    foreach($details['flids'] as $flid=>$type){
                        if($type=='Text'){
                            $text = TextField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                            if($text->text != '')
                                array_push($preview,$text->text);
                        }else if($type=='List'){
                            $list = ListField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                            if($list->option != '')
                                array_push($preview,$list->option);
                        }
                    }

                    $results[$kid] = $preview;
                }
            }

            return $results;
        }

        private static function search($pid, $fid, $arg, $method){
            $rids = [];

            $do_query = false;
            if (Session::has("query_assoc")) { // Have we ever searched before?
                $session_query = Session::get("query_assoc");
                $session_method = Session::get("method_assoc");
                $session_ids = Session::get("ids_assoc");

                if ($session_query == $arg && $session_method == $method && $session_ids == $pid.' '.$fid) { // This is the same search so we shouldn't re-execute the query.
                    $rids = unserialize(Session::get("rids_assoc"));
                }
                else { // This is a new search, so we have to execute again.
                    $do_query = true;
                }
            }
            else { // We have never searched before, so we must execute.
                $do_query = true;
            }

            if ($do_query) {
                // Inform the user about arguments that will be ignored.
                $ignored = Search::showIgnoredArguments($arg);
                $args = explode(" ", $arg);
                $args = array_diff($args, $ignored);
                $arg = implode(" ", $args);

                $search = new Search($pid, $fid, $arg, $method);

                $rids = $search->formKeywordSearch();

                sort($rids);

                Session::put("rids_assoc", serialize($rids));
            }

            Session::put("query_assoc", $arg);
            Session::put("method_assoc", $method);
            Session::put("ids_assoc", $pid.' '.$fid);

            return $rids;
        }
}