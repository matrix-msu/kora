@php
    $exists = \App\Http\Controllers\RecordController::exists($revision->kid);
    $data = \App\Http\Controllers\RevisionController::formatRevision($revision->id);
    $form = \App\Http\Controllers\FormController::getForm($revision->form_id);
    $type = ucfirst($revision->revision['type'] === "edit" ? 'edited' : ($revision->revision['type'] === 'rollback' ? 'rollback' : $revision->revision['type'].'d'));
@endphp
<div class="record card {{ $index == 0 ? 'active' : '' }}" id="{{$revision->kid}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title underline-middle-hover" href="{{ action("RevisionController@show",["pid" => $revision->project_id, "fid" => $revision->form_id, "rid" => $revision->record_kid]) }}">
                <span class="name">{{$revision->kid}}</span>
            </a>

        </div>

        <div class="card-toggle-wrap">
            <div class="left">
                <span class="sub-title">{{$type}}</span>
                <span class="sub-title">{{date_format($revision->created_at, "g:i")}}</span>
                <span class="sub-title">{{date_format($revision->created_at, "n.j.Y")}}</span>
                <span class="sub-title">{{$revision->owner}}</span>
            </div>
            <a href="#" class="card-toggle card-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
        <div class="description">
            @if (!$exists)
                <p class="deleted-description">
                    This record has been deleted,
                    but you still have the option to
                    <a class="underline-middle-hover reactivate-js" href="#" data-revision="{{$revision->id}}">re-activate the record</a>
                    to its previous state that is listed below.
                </p>
            @endif
            @if ($type === 'Edited' | $type === 'Rollback')
                <span>Edits Made</span>
                <div class="edit-section">
                    @foreach ($data["current"] as $id => $field)
                        <div class="field">
                            <div class="field-title">{{$form->layout['fields'][$id]['name']}}</div>
                            <div class="field-data">{!! $field !!}</div>
                        </div>
                    @endforeach
                </div>
                <span>Before</span>
                <div class="edit-section">
                    @foreach ($data["old"] as $id => $field)
                        <div class="field">
                            <div class="field-title">{{$form->layout['fields'][$id]['name']}}</div>
                            <div class="field-data">{!! $field !!}</div>
                        </div>
                    @endforeach
                </div>
            @else
                @foreach ($data as $id => $field)
                    <div class="field">
                        <div class="field-title">{{$form->layout['fields'][$id]['name']}}</div>
                        <div class="field-data">{!! $field !!}</div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="footer">
            @if (!isset($rid))
                <a class="quick-action underline-middle-hover left" href="{{action("RevisionController@show", ["pid" => $revision->project_id, "fid" => $revision->form_id, "rid" => $revision->record_kid])}}">
                    <span>See Revisions for this Record Only</span>
                </a>
            @endif

            @if ($exists)
                <a class="quick-action underline-middle-hover restore-js" href="#" data-revision="{{$revision->id}}">
                    <i class="icon icon-unarchive"></i>
                    <span>Restore Field(s) to Before</span>
                </a>
            @else
                <a class="quick-action underline-middle-hover reactivate-js" href="#" data-revision="{{$revision->id}}">
                    <i class="icon icon-unarchive"></i>
                    <span>Re-Activate Record</span>
                </a>
            @endif
        </div>
    </div>
</div>