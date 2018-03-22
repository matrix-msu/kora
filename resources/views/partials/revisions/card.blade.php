<?php 
    $exists = \App\Http\Controllers\RecordController::exists($revision->rid);
    $datetime = explode(' ', $revision->updated_at);
    $showLink = action("RevisionController@show", ["pid" => $form->pid, "fid" => $form->fid, "rid" => $revision->rid]);
    $type = ucfirst($revision->type === "edit" ? 'edited' : $revision->type.'d');
    $data = json_decode($revision->data, true);
?>
<div class="revision card all {{ $index == 0 ? 'active' : '' }}" id="{{$revision->id}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title{{ $exists ? '' : ' disabled' }}" href="{{ $exists ? $showLink : '' }}">
                <span class="name underline-middle-hover">{{$form->pid}}-{{$form->fid}}-{{$revision->rid}}</span>
            </a>
            <span class="sub-title">{{$type}}</span>
            <span class="sub-title">{{$datetime[1]}}</span>
            <span class="sub-title">{{$datetime[0]}}</span>
            <span class="sub-title">{{$revision->username}}</span>
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle revision-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content {{ $index == 0 ? 'active' : '' }}">
        <div class="description">
            @if ($type === 'Edited')
                <span>Edits Made</span>
                <div class="edit-section">
                    @foreach ($data as $type => $field)
                        
                    @endforeach
                    {{var_dump($data)}}
                </div>
                <span>Before</span>
            @else
                <p class="deleted-description">
                    This record has been deleted, 
                    but you still have the option to 
                    <a class="underline-middle-hover" href="#">re-activate the record</a> 
                    to its previous state that is listed below.
                </p>

                
            @endif
        </div>

        <div class="footer">
            <a class="quick-action underline-middle-hover left" href="$showLink">
                <span>See Revisions for this Record Only</span>
            </a>

            <a class="quick-action underline-middle-hover change-revision-js" revisionid="{{$revision->id}}" href="#">
                <i class="icon icon-edit-little"></i>
                <span>Change revision Name</span>
            </a>

            @if(\App\Http\Controllers\RecordController::exists($revision->rid))
                <a class="quick-action underline-middle-hover" href="{{ action("RecordController@show",
                    ["pid" => $form->pid, "fid" => $form->fid, "rid" => $revision->rid]) }}">
                    <span>View Original Record</span>
                    <i class="icon icon-arrow-right"></i>
                </a>
            @endif
        </div>
    </div>
</div>