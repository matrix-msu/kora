<?php
    $exists = \App\Http\Controllers\RecordController::exists($revision->rid);
    $datetime = explode(' ', $revision->updated_at);
    $showLink = action("RevisionController@show", ["pid" => $form->pid, "fid" => $form->fid, "rid" => $revision->rid]);
    $type = ucfirst($revision->type === "edit" ? 'edited' : $revision->type.'d');
    $data = \App\Http\Controllers\RevisionController::formatRevision($revision->id);
?>
<div class="revision card all {{ $index == 0 ? 'active' : '' }}" id="{{$revision->id}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            @if (!isset($rid))
                <a class="title{{ $exists ? '' : ' disabled' }}" href="{{ $exists ? action("RecordController@show", ['pid'=>$form->pid, 'fid'=>$form->fid, 'rid'=>$revision->rid]) : '' }}">
                    <span class="name underline-middle-hover">{{$form->pid}}-{{$form->fid}}-{{$revision->rid}}</span>
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
                <span class="sub-title">{{$datetime[1]}}</span>
                <span class="sub-title">{{$datetime[0]}}</span>
                <span class="sub-title">{{$revision->username}}</span>
            </span>
            <a href="#" class="card-toggle revision-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content {{ $index == 0 ? 'active' : '' }}">
        <div class="description">
            @if (!$exists)
                <p class="deleted-description">
                    This record has been deleted, 
                    but you still have the option to 
                    <a class="underline-middle-hover" href="#">re-activate the record</a> 
                    to its previous state that is listed below.
                </p>
            @endif
            @if ($type === 'Edited')
                <span>Edits Made</span>
                <div class="edit-section">
                    @foreach ($data["current"] as $id => $field)
                        <div class="field">
                            <div class="field-title">{{$field["name"]}}</div>
                            <div class="field-data">{!! $field["data"] !!}</div>
                        </div>
                    @endforeach
                </div>
                <span>Before</span>
                <div class="edit-section">
                    @foreach ($data["old"] as $id => $field)
                        <div class="field">
                            <div class="field-title">{{$field["name"]}}</div>
                            <div class="field-data">{!! $field["data"] !!}</div>
                        </div>
                    @endforeach
                </div>
            @else
                @foreach ($data as $id => $field)
                    <div class="field">
                        <div class="field-title">{{$field["name"]}}</div>
                        <div class="field-data">{!! $field["data"] !!}</div>
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

            @if ($exists)
                <a class="quick-action underline-middle-hover" href="{{ action("RecordController@show",
                    ["pid" => $form->pid, "fid" => $form->fid, "rid" => $revision->rid]) }}">
                    <i class="icon icon-unarchive"></i>
                    <span>Restore Field(s) to Before</span>
                </a>
            @else
               <a class="quick-action underline-middle-hover" href="{{ action("RecordController@show",
                    ["pid" => $form->pid, "fid" => $form->fid, "rid" => $revision->rid]) }}">
                    <i class="icon icon-unarchive"></i>
                    <span>Re-Activate Record</span>
                </a>
            @endif
        </div>
    </div>
</div>