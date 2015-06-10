<div class="panel panel-default">
    <div class="panel-heading" style="font-size: 1.5em;">
        <a href="{{ action('FieldController@show',['pid' => $field->pid,'fid' => $field->fid, 'flid' => $field->flid]) }}">{{ $field->name }}</a>
        <span  class="pull-right">{{ $field->type }}</span>
    </div>
    <div class="collapseTest" style="display:none">
        <div class="panel-body"><b>Description:</b> {{ $field->desc }}</div>
        <div class="panel-footer">
                <span>
                    <a href="{{ action('FieldController@edit',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">[Edit]</a>
                </span>
                <span>
                    <a href="{{ action('FieldController@show',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">[Options]</a>
                </span>
                <span>
                    <a onclick="deleteField('{{ $field->name }}', {{ $field->flid }})" href="javascript:void(0)">[Delete]</a>
                </span>
        </div>
    </div>
</div>