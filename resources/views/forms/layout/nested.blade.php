<div id="node" style="margin-left:50px">
    <h3>{{ $title }}</h3>
    @if(\Auth::user()->canCreateFields($form) && isset($layoutPage))
        <div class="form-inline">
            <form action="{{action('FormController@addNode', ['pid' => $form->pid, 'fid' => $form->fid]) }}"
                  method="POST" class="form-group form-inline">
                <input type="hidden" value="{{ csrf_token() }}" name="_token">
                <input type="hidden" value="{{$title}}" name="nodeTitle">
                <input type="text" name="name" class = "form-control" required/>
                <input type="submit" value="{{trans('forms_layout_nested.create')}}" class="btn form-control">
            </form>
            <form action="{{action('FormController@deleteNode', ['pid' => $form->pid, 'fid' => $form->fid, 'title' => $title]) }}"
                  method="POST" class="form-group form-inline">
                <input type="hidden" value="{{ csrf_token() }}" name="_token">
                <input type="submit" value="{{trans('forms_layout_nested.delete')}}" class="btn btn-danger form-control">
            </form>
        </div>
        <br />
    @endif
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