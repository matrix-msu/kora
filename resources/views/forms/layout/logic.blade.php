<?php
    $vals = \App\Http\Controllers\FormController::xmlToArray($form->layout);
?>
@for($i=0;$i<sizeof($vals);$i++)
    @if($vals[$i]['tag']=='ID')
        @include($fieldview,['field' => App\Field::where('flid', '=', $vals[$i]['value'])->first()])
    @elseif($vals[$i]['tag']=='NODE')
        <?php
        $level = $vals[$i]['level'];
        $title = $vals[$i]['attributes']['TITLE'];
        $node = array();
        $i++;
        while($vals[$i]['tag']!='NODE' | $vals[$i]['type']!='close' | $vals[$i]['level']!=$level){
            array_push($node,$vals[$i]);
            $i++;
        }
        ?>
        @include('forms.layout.nested',['node' => $node, 'title' => $title, 'fieldview' => $fieldview])
    @endif
@endfor