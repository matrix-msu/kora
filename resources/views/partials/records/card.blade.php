<div class="record card all active form-group" id="{{$record->id}}">
    <div class="header active check-box">
        <span class="check ml-xxs mt-xxs"></span>
        <div class="left pl-xxxl">
            <a class="title underline-middle-hover" href="{{ action("RecordController@show",
                ["pid" => $record->pid, "fid" => $record->fid, "rid" => $record->rid]) }}">
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
            @foreach(\App\Http\Controllers\PageController::getFormLayout($record->fid) as $page)
                <section class="record-page mt-xxxl">
                    <div class="record-page-title">{{$page["title"]}}</div>
                    <div class="record-page-spacer mt-xs"></div>
                    @if($page["fields"]->count() > 0)
                        @foreach($page["fields"] as $field)
                            @if($field->viewresults)
                                <div class="field-title mt-xl">{{$field->name}}</div>

                                <section class="field-data">
                                    <?php $typedField = $field->getTypedFieldFromRID($record->rid); ?>
                                    @if(!is_null($typedField))
                                        @include($typedField::FIELD_DISPLAY_VIEW, ['field' => $field, 'typedField' => $typedField])
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
            <a class="quick-action trash-container left danger delete-record-js tooltip" rid="{{$record->rid}}" href="#" tooltip="Delete Record">
                <i class="icon icon-trash"></i>
            </a>

            <a class="quick-action underline-middle-hover" href="{{action('RevisionController@show',
                        ['pid' => $field->pid, 'fid' => $field->fid, 'rid' => $record->rid])}}">
                <i class="icon icon-clock-little"></i>
                <span>View Revisions</span>
            </a>

            <a class="quick-action underline-middle-hover" href="{{action('RecordController@cloneRecord', [
                        'pid' => $field->pid, 'fid' => $field->fid, 'rid' => $record->rid])}}">
                <i class="icon icon-duplicate-little"></i>
                <span>Duplicate Records</span>
            </a>

            <a class="quick-action underline-middle-hover" href="{{ action('RecordController@edit',
                        ['pid' => $field->pid, 'fid' => $field->fid, 'rid' => $record->rid]) }}">
                <i class="icon icon-edit-little"></i>
                <span>Edit</span>
            </a>

            <a class="quick-action underline-middle-hover" href="{{ action("RecordController@show",
                ["pid" => $record->pid, "fid" => $record->fid, "rid" => $record->rid]) }}">
                <span>View Record</span>
                <i class="icon icon-arrow-right"></i>
            </a>
        </div>
    </div>
</div>