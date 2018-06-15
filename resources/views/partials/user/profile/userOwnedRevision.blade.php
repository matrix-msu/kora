<?php
    $exists = \App\Http\Controllers\RecordController::exists($revision->rid);
    $data = \App\Http\Controllers\RevisionController::formatRevision($revision->id);
?>
<div class="record card {{ $index == 0 ? 'active' : '' }}" id="{{$revision->rid}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title underline-middle-hover" href="{{ action("RevisionController@show",["pid" => $revision['pid'], "fid" => $revision['fid'], "rid" => $revision['rid']]) }}">
                <span>{{$revision->kid}}</span>
            </a>

        </div>

        <div class="card-toggle-wrap">
            <div class="left">
                <span class="sub-title">{{$revision->type}}</span>
                <span class="sub-title">{{date_format($revision->created_at, "g:i")}}</span>
                <span class="sub-title">{{date_format($revision->created_at, "n.j.Y")}}</span>
                @if ($revision->username)<span class="sub-title">{{$revision->username}}</span>@endif
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
            @if ($revision->type === 'edit')
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
                <a class="quick-action underline-middle-hover left" href="{{action("RevisionController@show", ["pid" => $revision->pid, "fid" => $revision->fid, "rid" => $revision->rid])}}">
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