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

    public static function navButtonsAllowed($layout, $flid){
        $vis = ['up'=>false,'down'=>false,'upIn'=>false,'downIn'=>false,'upOut'=>false,'downOut'=>false];

        $layout = FormController::xmlToArray($layout);

        $fieldTag=0;
        for($i=0;$i<sizeof($layout);$i++){
            if($layout[$i]['tag']=='ID' && $layout[$i]['value']==$flid){
                $fieldTag = $i;
                break;
            }
        }

        //up - if close node or field directly above
        if(isset($layout[$fieldTag-1]) && $layout[$fieldTag-1]['tag']=='ID'){
            $vis['up'] = true;
        }else if(isset($layout[$fieldTag-1]) && $layout[$fieldTag-1]['tag']=='NODE' && $layout[$fieldTag-1]['type']=='close'){
            $vis['up'] = true;
        }

        //down - if open node or field directly below
        if(isset($layout[$fieldTag+1]) && $layout[$fieldTag+1]['tag']=='ID'){
            $vis['down'] = true;
        }else if(isset($layout[$fieldTag+1]) && $layout[$fieldTag+1]['tag']=='NODE' && $layout[$fieldTag+1]['type']=='open'){
            $vis['down'] = true;
        }

        //upIn - if close node with same lvl is directly above
        if(isset($layout[$fieldTag-1]) && $layout[$fieldTag-1]['tag']=='NODE' && $layout[$fieldTag-1]['type']=='close' && $layout[$fieldTag-1]['level']==$layout[$fieldTag]['level']){
            $vis['upIn'] = true;
        }

        //downIn - if open node with same lvl is directly below
        if(isset($layout[$fieldTag+1]) && $layout[$fieldTag+1]['tag']=='NODE' && $layout[$fieldTag+1]['type']=='open' && $layout[$fieldTag+1]['level']==$layout[$fieldTag]['level']){
            $vis['downIn'] = true;
        }

        //upOut - if first open node with one lvl less is above
        for($j=$fieldTag-1;$j>=0;$j--){
            if(isset($layout[$j]) && $layout[$j]['tag']=='NODE' && $layout[$j]['type']=='open' && $layout[$j]['level']==$layout[$fieldTag]['level']-1){
                $vis['upOut'] = true;
                break;
            }
        }

        //downOut - if first close node with one lvl less is below
        for($j=$fieldTag+1;$j<sizeof($layout);$j++){
            if(isset($layout[$j]) && $layout[$j]['tag']=='NODE' && $layout[$j]['type']=='close' && $layout[$j]['level']==$layout[$fieldTag]['level']-1){
                $vis['downOut'] = true;
                break;
            }
        }

        return $vis;
    }
}
