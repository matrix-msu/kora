 <div class="panel panel-default">
    <div class="panel-heading" style="font-size: 1.5em;">
        @if($field->type=='Associator' and sizeof(\App\Http\Controllers\AssociationController::getAvailableAssociations($field->fid))==0)
            <font color="red">{{$field->name}}</font>
        @elseif(\Auth::user()->canEditFields($form))
            <a href="{{ action('FieldController@show',['pid' => $field->pid,'fid' => $field->fid, 'flid' => $field->flid]) }}">{{ $field->name }}</a>
        @else
            {{$field->name}}
        @endif
        <span  class="pull-right">{{ App\Services\Translator::translate($field->type) }} </span>
    </div>
    <div class="collapseTest" style="display:none">
        <div class="panel-body">
            <b>{{trans('projects_show.name')}}:</b> {{ $field->slug }}<br>
            <b>{{trans('forms_layout_printfield.desc')}}:</b> {{ $field->desc }}
        </div>
        <div class="panel-footer">

            @if(\Auth::user()->canEditFields($form))
                <span>
                    <a href="{{ action('FieldController@edit',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">[{{trans('forms_layout_printfield.edit')}}]</a>
                </span>
                <span>
                    <a href="{{ action('FieldController@show',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">[{{trans('forms_layout_printfield.options')}}]</a>
                </span>
            @endif
            @if(\Auth::user()->canDeleteFields($form))
                <span>
                    <a onclick="deleteField('{{ $field->name }}', {{ $field->flid }})" href="javascript:void(0)">[{{trans('forms_layout_printfield.delete')}}]</a>
                </span>
            @endif
                <span  class="pull-right">
                    @include('forms.layout.navbuttons',['layout'=>\App\Http\Controllers\FieldNavController::navButtonsAllowed($form->layout, $field->flid)])
                </span>
        </div>
    </div>
</div>