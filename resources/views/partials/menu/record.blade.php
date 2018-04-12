<li class="navigation-item">
    <a href="#" class="menu-toggle navigation-toggle-js">
        <i class="icon icon-minus mr-sm"></i>
        <span>{{ $record->kid }}</span>
        <i class="icon icon-chevron"></i>
    </a>

    <ul class="navigation-sub-menu navigation-sub-menu-js">
        <li class="link link-head">
            <a href="{{action("RecordController@show", ['pid'=>$record->pid,'fid'=>$record->fid,'rid'=>$record->rid])}}">
                <i class="icon icon-record"></i>
                <span>{{ $record->kid }}</span>
            </a>
        </li>

        <li class="spacer full"></li>

        <li class="link first">
            <a href="{{action("RecordController@edit", ['pid'=>$record->pid, 'fid'=>$record->fid, 'rid'=>$record->rid])}}">Edit Record</a>
        </li>

        <li class="link">
            <a href="{{action('RecordController@cloneRecord', ['pid'=>$record->pid, 'fid'=>$record->fid, 'rid'=>$record->rid])}}">Duplicate Record</a>
        </li>

        <li class="link">
            <a href="{{action("RevisionController@show", ['pid'=>$record->pid, 'fid'=>$record->fid, 'rid'=>$record->rid])}}">View Revisions ({{\App\Http\Controllers\RevisionController::getRevisionCount($record->rid)}})</a>
        </li>

        <li class="link">
            <a href="#">Designate as Preset</a>
        </li>
    </ul>
</li>