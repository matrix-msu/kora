<div class="drawer-element drawer-element-js">
    <a href="#" class="drawer-toggle drawer-toggle-js">
        <i class="icon icon-record"></i>
        <span>{{ $pid . '-' . $fid . '-' . $rid }}</span>
        <i class="icon icon-chevron"></i>
    </a>

    <ul class="drawer-content drawer-content-js">
        <li class="content-link head">
            <a href="{{action("RecordController@show", ['pid'=>$pid,'fid'=>$fid,'rid'=>$rid])}}">
                <span>{{ $pid . '-' . $fid . '-' . $rid }}</span>
            </a>
        </li>

        <li class="content-link">
            <a href="{{action("RecordController@edit", ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">Edit Record</a>
        </li>

        <li class="content-link">
            <a href="{{action('RecordController@cloneRecord', ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">Duplicate Record</a>
        </li>

        <li class="content-link">
            <a href="{{action("RevisionController@show", ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">View Revisions ({{\App\Http\Controllers\RevisionController::getRevisionCount($rid)}})</a>
        </li>

        <li class="content-link">
            <a href="#">Designate as Preset</a>
        </li>
    </ul>
</div>
