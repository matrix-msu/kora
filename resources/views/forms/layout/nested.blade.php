<div id="node" style="margin-left:50px">
    <h3>{{ $title }}</h3>
    @for($i=0;$i<sizeof($node);$i++)
        @if($node[$i]['tag']=='ID')
            @include($fieldview,['field' => App\Field::where('flid', '=', $node[$i]['value'])->first()])
        @elseif($node[$i]['tag']=='NODE')
            <?php
            $level = $node[$i]['level'];
            $title = $node[$i]['attributes']['TITLE'];
            $node2 = array();
            $i++;
            while($node[$i]['tag']!='NODE' | $node[$i]['type']!='close' | $node[$i]['level']!=$level){
                array_push($node2,$node[$i]);
                $i++;
            }
            ?>
            @include('forms.layout.nested',['node' => $node2, 'title' => $title, 'fieldview' => $fieldview])
        @endif
    @endfor
</div>