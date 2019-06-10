<div class="drawer-element drawer-element-js">
    <a href="#" class="drawer-toggle drawer-toggle-js" data-drawer="{{ $openDrawer or '0' }}">
        <i class="icon icon-record"></i>
        <span>{{ $pid . '-' . $fid . '-' . $rid }}</span>
        <i class="icon icon-chevron"></i>
    </a>

    <ul class="drawer-content drawer-content-js">
        <li class="content-link content-link-js" data-page="record-show">
            <a href="{{action("RecordController@show", ['pid'=>$pid,'fid'=>$fid,'rid'=>$rid])}}">
                <span>{{ $pid . '-' . $fid . '-' . $rid }}</span>
            </a>
        </li>

        <li class="content-link content-link-js" data-page="record-edit">
            <a href="{{action("RecordController@edit", ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">Edit Record</a>
        </li>

        <li class="content-link content-link-js" data-page="record-clone">
            <a href="{{action('RecordController@cloneRecord', ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">Duplicate Record</a>
        </li>

        <li class="content-link content-link-js" data-page="record-revisions">
            <a href="{{action("RevisionController@show", ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">View Revisions ({{\App\Http\Controllers\RevisionController::getRevisionCount($pid . '-' . $fid . '-' . $rid)}})</a>
        </li>

        <li class="content-link content-link-js">
          @if(\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
                {{--TODO::CASTLE--}}
            <?php
                $alreadyPreset = false;
                //$alreadyPreset = (\App\RecordPreset::where('rid',$rid)->count() > 0);
            ?>
            @if($alreadyPreset)
              <a class="already-preset-js" href="#">Designated as Preset</a>
            @else
              <a class="designate-preset-js" href="#">Designate as Preset</a>
            @endif
          @endif
        </li>
    </ul>
</div>
