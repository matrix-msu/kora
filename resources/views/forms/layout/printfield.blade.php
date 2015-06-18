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
                <span  class="pull-right">
                    <button onclick="moveFieldUp({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-15.png" alt="Move Up"></button>
                    <button onclick="moveFieldDown({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-14.png" alt="Move Down"></button>
                    <button onclick="moveFieldUpIn({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-12.png" alt="Move Up and In"></button>
                    <button onclick="moveFieldDownIn({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-13.png" alt="Move Down and In"></button>
                    <button onclick="moveFieldUpOut({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-10.png" alt="Move Up and Out"></button>
                    <button onclick="moveFieldDownOut({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-11.png" alt="Move Down and Out"></button>
                </span>
        </div>
    </div>
</div>