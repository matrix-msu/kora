<li class="navigation-item">
    <a href="{{action("RecordController@show", ['pid'=>$record->pid,'fid'=>$record->fid,'rid'=>$record->rid])}}" class="menu-toggle navigation-toggle-js">
        <i class="icon icon-minus mr-sm"></i>
        <span>{{ $record->kid }}</span>
    </a>
</li>