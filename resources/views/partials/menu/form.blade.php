<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ \App\Http\Controllers\FormController::getForm($fid)->name }}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid}}">Form Layout</a></li>
        <?php $allowed_forms = \Auth::user()->allowedForms($pid) ?>
        @if(sizeof($allowed_forms) > 1)
            <li class="dropdown-submenu" id="form-submenu"> <a href="#" data-toggle="dropdown">Jump to Form</a>
                <ul class="dropdown-menu">
                    @foreach($allowed_forms as $form)
                        @if($form->fid != $fid)
                            <li><a href="{{ url('/projects/'.$pid).'/forms/'.$form->fid }}">{{ $form->name }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif
        <li class="divider"></li>
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/records'}}">Records</a></li>
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/records/create'}}">New Record</a></li>
        <li><a href="{{ action('RecordController@showMassAssignmentView',['pid' => $pid, 'fid' => $fid]) }}">Mass Assign Records</a></li>
        <li class="divider"></li>
        <li><a href="{{url('/projects/'.$pid).'/forms/'.$fid.'/metadata/setup'}}">Linked Open Data</a></li>
        @if (\Auth::user()->admin || \Auth::user()->isFormAdmin(\App\Http\Controllers\FormController::getForm($fid)))
            <li class="divider"></li>
            <li><a href="{{action('FormGroupController@index', ['pid'=>$pid, 'fid'=>$fid])}}">Manage Groups</a></li>
            <li><a href="{{action('AssociationController@index', ['fid'=>$fid, 'pid'=>$pid])}}">Manage Associations</a></li>
            <li><a href="{{action('RevisionController@index', ['pid'=>$pid, 'fid'=>$fid])}}">Manage Record Revisions</a></li>
            <li><a href="{{action('RecordPresetController@index', ['pid'=>$pid, 'fid'=>$fid])}}">Manage Record Presets</a></li>
        @endif
    </ul>
</li>