@php
    $exists = \App\Http\Controllers\RecordController::exists($revision->record_kid);
    $record_id = explode('-',$revision->record_kid)[2];
    $datetime = explode(' ', $revision->updated_at);
    $showLink = action("RevisionController@show", ["pid" => $form->project_id, "fid" => $form->id, "rid" => $record_id]);
    $type = ucfirst($revision->revision['type'] === "edit" ? 'edited' : ($revision->revision['type'] === 'rollback' ? 'rollback' : $revision->revision['type'].'d'));
    $data = \App\Http\Controllers\RevisionController::formatRevision($revision->id);
@endphp
<div class="revision card all {{ $index == 0 ? 'active' : '' }}" id="{{$revision->id}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            @if (!isset($rid))
                <a class="title{{ $exists ? '' : ' disabled' }}" href="{{ $exists ? action("RecordController@show", ['pid'=>$form->project_id, 'fid'=>$form->id, 'rid'=>$record_id]) : '' }}">
                    <span class="name underline-middle-hover">{{$revision->record_kid}}</span>
                </a>
            @else
                <span class="title gray">
                    <span class="name">{{$type}}</span>
                </span>
            @endif
        </div>

        <div class="card-toggle-wrap">
            <span class="left pl-m">
                @if (!isset($rid))
                    <span class="sub-title">{{$type}}</span>
                @endif
                <span class="sub-title time-js">{{$datetime[1]}}</span>
                <span class="sub-title date-js">{{$datetime[0]}}</span>
                <span class="sub-title">{{$revision->owner}}</span>
            </span>
            <a href="#" class="card-toggle revision-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content {{ $index == 0 ? 'active' : '' }}">
        <div class="description">
            @if(!$exists && $revision->rollback)
                <p class="deleted-description">
                    This record has been deleted,
                    but you still have the option to
                    <a class="underline-middle-hover reactivate-js" href="#" data-revision="{{$revision->id}}">re-activate the record</a>
                    to its previous state that is listed below.
                </p>
            @elseif(!$exists)
                <p class="deleted-description">
                    This record has been deleted, however this particular version of the Record is not available for rollback.
                </p>
            @endif
            @if ($type === 'Edited' | $type === 'Rollback')
                <span>Edits Made</span>
                <div class="edit-section">
                    @foreach ($data["current"] as $id => $field)
                        @if($field != $data["old"][$id])
                            <div class="field">
                                <div class="field-title">{{$form->layout['fields'][$id]['name']}}</div>
                                <div class="field-data">{{ $field }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <span>Before</span>
                <div class="edit-section">
                    @foreach ($data["old"] as $id => $field)
                        @if($field != $data["current"][$id])
                            <div class="field">
                                <div class="field-title">{{$form->layout['fields'][$id]['name']}}</div>
                                <div class="field-data">{{ $field }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                @foreach ($data as $id => $field)
                    <div class="field">
                        <div class="field-title">{{$form->layout['fields'][$id]['name']}}</div>
                        <div class="field-data">{{ $field }}</div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="footer">
            @if (!isset($rid))
                <a class="quick-action underline-middle-hover left" href="{{$showLink}}">
                    <span>See Revisions for this Record Only</span>
                </a>
            @endif

            @if($exists && $revision->rollback)
                <a class="quick-action underline-middle-hover restore-js" href="#" data-revision="{{$revision->id}}">
                    <i class="icon icon-unarchive"></i>
                    <span>Restore Field(s) to Before</span>
                </a>
            @elseif($revision->rollback)
               <a class="quick-action underline-middle-hover reactivate-js" href="#" data-revision="{{$revision->id}}">
                    <i class="icon icon-unarchive"></i>
                    <span>Re-Activate Record</span>
               </a>
            @else
                <a class="quick-action disabled">
                    <span>Rollback disabled</span>
                </a>
            @endif
        </div>
    </div>
</div>
