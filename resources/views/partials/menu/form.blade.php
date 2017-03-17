<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ \App\Http\Controllers\FormController::getForm($fid)->name }}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid}}">{{trans('partials_menu_form.layout')}}</a></li>
        <?php $allowed_forms = \Auth::user()->allowedForms($pid) ?>
        @if(sizeof($allowed_forms) > 1)
            <li class="dropdown-submenu" id="form-submenu"> <a href="#" data-toggle="dropdown">{{trans('partials_menu_form.jump')}}</a>
                <ul class="dropdown-menu scrollable-submenu">
                    @foreach($allowed_forms as $form)
                        @if($form->fid != $fid)
                            <li><a href="{{ url('/projects/'.$pid).'/forms/'.$form->fid }}">{{ $form->name }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        @if(isset($passed_field))
            <?php
            $fieldsInForm = \App\Field::where('fid', '=', $fid)->get()->all();
            $cnt = sizeof($fieldsInForm);
            ?>
            @if($cnt > 1)
                <li class="dropdown-submenu" id="field-submenu"> <a href="#" data-toggle="dropdown">{{trans('partials_menu_options.jump')}}</a>
                    <ul class="dropdown-menu scrollable-submenu">
                        @foreach($fieldsInForm as $field)
                            @if($field->flid != $passed_field->flid)
                                <li><a href="{{ url('/projects/'.$pid).'/forms/'.$field->fid .'/fields/'.$field->flid.'/options'}}">{{ $field->name }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </li>
            @endif
        @endif

        <li class="divider"></li>
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/records'}}">{{trans('partials_menu_form.records')}}</a></li>
        @if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($fid)))
            <li><a href="{{ action('RecordController@create',['pid' => $pid, 'fid' => $fid]) }}">{{trans('partials_menu_form.newrec')}}</a></li>
            <li><a href="{{ action('RecordController@importRecordsView',['pid' => $pid, 'fid' => $fid]) }}">{{trans('partials_menu_form.import')}}</a></li>
        @endif
        <li><a href="{{ action('RecordController@showMassAssignmentView',['pid' => $pid, 'fid' => $fid]) }}">{{trans('partials_menu_form.massassign')}}</a></li>
        <li class="divider"></li>
        <li><a href="{{url('/projects/'.$pid).'/forms/'.$fid.'/metadata/setup'}}">{{trans('partials_menu_form.lod')}}</a></li>
        @if (\Auth::user()->admin || \Auth::user()->isFormAdmin(\App\Http\Controllers\FormController::getForm($fid)))
            <li class="divider"></li>
            <li><a href="{{action('FormGroupController@index', ['pid'=>$pid, 'fid'=>$fid])}}">{{trans('partials_menu_form.groups')}}</a></li>
            <li><a href="{{action('AssociationController@index', ['fid'=>$fid, 'pid'=>$pid])}}">{{trans('partials_menu_form.assoc')}}</a></li>
            <li><a href="{{action('RevisionController@index', ['pid'=>$pid, 'fid'=>$fid])}}">{{trans('partials_menu_form.revisions')}}</a></li>
            <li><a href="{{action('RecordPresetController@index', ['pid'=>$pid, 'fid'=>$fid])}}">{{trans('partials_menu_form.presets')}}</a></li>
        @endif
    </ul>
</li>