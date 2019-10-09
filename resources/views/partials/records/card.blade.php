<div class="record card all active form-group" id="{{$record->id}}">
    <div class="header active check-box">

        @if (is_null(app('request')->input('projects')) && is_null(app('request')->input('forms')))
          <span class="check ml-xxs mt-xxs"></span>
        @endif
        <div class="left @if (is_null(app('request')->input('projects')) && is_null(app('request')->input('forms'))) pl-xxxl @else pl-xl @endif">
            <a class="title underline-middle-hover" href="{{ action("RecordController@show",
                ["pid" => $record->project_id, "fid" => $record->form_id, "rid" => $record->id]) }}">
                <span class="name">{{$record->kid}}</span>
                <i class="icon icon-arrow-right"></i>
            </a>
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle record-toggle-js">
                <i class="icon icon-chevron active"></i>
            </a>
        </div>
    </div>

    <div class="content active">
        <div class="description">
            @foreach($form->layout['pages'] as $page)
                <section class="record-page mt-xxxl">
                    <div class="record-page-title">{{$page["title"]}}</div>
                    <div class="record-page-spacer mt-xs"></div>
                    @if(sizeof($page["flids"]) > 0)
                        @foreach($page["flids"] as $flid)
                            @php $field = $form->layout['fields'][$flid]; @endphp
                            @if($field['viewable_in_results'])
                                <div class="field-title mt-xl">{{$field['name']}}</div>

                                <section class="field-data">
                                    @php $typedField = $form->getFieldModel($field['type']); @endphp
                                    @if(!is_null($record->{$flid}))
                                        @include($typedField->getFieldDisplayView(), ['field' => $field, 'typedField' => $typedField, 'value' => $record->{$flid}])
                                    @else
                                        <span class="record-no-data">No Data Inputted</span>
                                    @endif
                                </section>
                            @endif
                        @endforeach
                    @else
                        <div class="field-title no-field mt-xl">No fields added to this page</div>
                    @endif
                </section>
            @endforeach
        </div>

        <div class="footer">
            <a class="quick-action trash-container left danger delete-record-js tooltip" rid="{{$record->id}}" rev-assoc-count="{{$record->getAssociatedRecordsCount()}}" href="#" tooltip="Delete Record">
                <i class="icon icon-trash"></i>
            </a>

            <a class="quick-action underline-middle-hover" href="{{action('RevisionController@show',
                        ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id])}}">
                <i class="icon icon-clock-little"></i>
                <span>View Revisions</span>
            </a>

            <a class="quick-action underline-middle-hover" href="{{action('RecordController@cloneRecord', [
                        'pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id])}}">
                <i class="icon icon-duplicate-little"></i>
                <span>Duplicate Records</span>
            </a>

            <a class="quick-action underline-middle-hover" href="{{ action('RecordController@edit',
                        ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id]) }}">
                <i class="icon icon-edit-little"></i>
                <span>Edit</span>
            </a>

            <a class="quick-action underline-middle-hover" href="{{ action("RecordController@show",
                ["pid" => $record->project_id, "fid" => $record->form_id, "rid" => $record->id]) }}">
                <span>View Record</span>
                <i class="icon icon-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
