<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class FieldNavController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
        //These functions deal with field nav
        $field = \App\Http\Controllers\FieldController::getField($request->flid);
        $form = \App\Http\Controllers\FormController::getForm($field->fid);

        $vals = FormController::xmlToArray($form->layout);

        if($request->action=='moveFieldUp') {
            for ($i = 0; $i < sizeof($vals); $i++) {
                if(isset($vals[$i]['value']) && $vals[$i]['value']==$field->flid){
                    //if we have a field above us
                    if($vals[$i-1]['tag']=='ID'){
                        $temp = $vals[$i];
                        $vals[$i] = $vals[$i-1];
                        $vals[$i-1] = $temp;
                    }
                    //if we have a node above us
                    if($vals[$i-1]['tag']=='NODE' && $vals[$i-1]['type']=='close'){
                        $j = $i-1;
                        $lvl = $vals[$i-1]['level'];
                        while($j>0){
                            if($vals[$j]['tag']=='NODE' && $vals[$j]['type']=='open' && $vals[$j]['level']==$lvl){
                                $k=$j; //this is the start of the node
                                break;
                            }else{
                                $j--;
                            }
                        }
                        $temp = $vals[$i];
                        while($i>$k){
                            $vals[$i] = $vals[$i-1];
                            $i--;
                        }
                        $vals[$i] = $temp;
                    }

                    $form->layout = $this->valsToXML($vals);
                    $form->save();
                    break;
                }
            }
        }

        if($request->action=='moveFieldDown') {
            for ($i = 0; $i < sizeof($vals); $i++) {
                if(isset($vals[$i]['value']) && $vals[$i]['value']==$field->flid){
                    //if we have a field below us
                    if($vals[$i+1]['tag']=='ID'){
                        $temp = $vals[$i];
                        $vals[$i] = $vals[$i+1];
                        $vals[$i+1] = $temp;
                    }
                    //if we have a node below us
                    if($vals[$i+1]['tag']=='NODE' && $vals[$i+1]['type']=='open'){
                        $j = $i+1;
                        $lvl = $vals[$i+1]['level'];
                        while($j<sizeof($vals)){
                            if($vals[$j]['tag']=='NODE' && $vals[$j]['type']=='close' && $vals[$j]['level']==$lvl){
                                $k=$j; //this is the start of the node
                                break;
                            }else{
                                $j++;
                            }
                        }
                        $temp = $vals[$i];
                        while($i<$k){
                            $vals[$i] = $vals[$i+1];
                            $i++;
                        }
                        $vals[$i] = $temp;
                    }

                    $form->layout = $this->valsToXML($vals);
                    $form->save();
                    break;
                }
            }
        }

        if($request->action=='moveFieldUpIn') {
            for ($i = 0; $i < sizeof($vals); $i++) {
                if(isset($vals[$i]['value']) && $vals[$i]['value']==$field->flid) {
                    //if the Node is above us
                    if($vals[$i-1]['tag']=='NODE' && $vals[$i-1]['type']=='close') {
                        $tmp = $vals[$i];
                        $vals[$i] = $vals[$i-1];
                        $vals[$i-1] = $tmp;
                    }

                    $form->layout = $this->valsToXML($vals);
                    $form->save();
                    break;
                }
            }
        }

        if($request->action=='moveFieldDownIn') {
            for ($i = 0; $i < sizeof($vals); $i++) {
                if(isset($vals[$i]['value']) && $vals[$i]['value']==$field->flid) {
                    //if the Node is above us
                    if($vals[$i+1]['tag']=='NODE' && $vals[$i+1]['type']=='open') {
                        $tmp = $vals[$i];
                        $vals[$i] = $vals[$i+1];
                        $vals[$i+1] = $tmp;
                    }

                    $form->layout = $this->valsToXML($vals);
                    $form->save();
                    break;
                }
            }
        }

        if($request->action=='moveFieldUpOut') {
            for ($i = 0; $i < sizeof($vals); $i++) {
                if(isset($vals[$i]['value']) && $vals[$i]['value']==$field->flid) {
                    //if we have a node above us
                    $j=$i-1;
                    $lvl = $vals[$i]['level']-1;
                    while ($j>0) {
                        if ($vals[$j]['tag'] == 'NODE' && $vals[$j]['type'] == 'open' && $vals[$j]['level']==$lvl) {
                            $k = $j;
                            break;
                        }else{
                            $j--;
                        }
                    }

                    $temp = $vals[$i];
                    while($i>$k){
                        $vals[$i] = $vals[$i-1];
                        $i--;
                    }
                    $vals[$i] = $temp;

                    $form->layout = $this->valsToXML($vals);
                    $form->save();
                    break;
                }
            }
        }

        if($request->action=='moveFieldDownOut') {
            for ($i = 0; $i < sizeof($vals); $i++) {
                if(isset($vals[$i]['value']) && $vals[$i]['value']==$field->flid) {
                    //if we have a node below us
                    $j=$i+1;
                    $lvl = $vals[$i]['level']-1;
                    while ($j<sizeof($vals)) {
                        if ($vals[$j]['tag'] == 'NODE' && $vals[$j]['type'] == 'close' && $vals[$j]['level']==$lvl) {
                            $k = $j;
                            break;
                        }else{
                            $j++;
                        }
                    }

                    $temp = $vals[$i];
                    while($i<$k){
                        $vals[$i] = $vals[$i+1];
                        $i++;
                    }
                    $vals[$i] = $temp;

                    $form->layout = $this->valsToXML($vals);
                    $form->save();
                    break;
                }
            }
        }

    }

    public function valsToXML($vals){
        $xml = '';

        foreach($vals as $node){
            if($node['type']=='open'){
                $tag = '<'.$node['tag'];
                if(isset($node['attributes'])){
                    $tag .= " title='".$node['attributes']['TITLE']."'";
                }
                $tag .= '>';
                $xml .= $tag;
            } else if($node['type']=='close'){
                $tag = '</'.$node['tag'].'>';
                $xml .= $tag;
            } else if($node['type']=='complete'){
                $tag = '<'.$node['tag'].'>'.$node['value'].'</'.$node['tag'].'>';
                $xml .= $tag;
            }
        }

        return $xml;
    }

    public static function navButtonsAllowed($layout){
        $vis = ['up'=>false,'down'=>false,'upIn'=>false,'downIn'=>false,'upOut'=>false,'downOut'=>false];

        //do stuff
        $layout = FormController::xmlToArray($layout);
        //dd($layout);

        return $vis;
    }
}
