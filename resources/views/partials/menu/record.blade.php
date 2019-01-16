<li class="navigation-item">
    <a href="#" class="menu-toggle navigation-toggle-js">
        <i class="icon icon-minus mr-sm"></i>
        <span>{{ $pid . '-' . $fid . '-' . $rid }}</span>
        <i class="icon icon-chevron"></i>
    </a>

    <ul class="navigation-sub-menu navigation-sub-menu-js">
        <li class="link link-head">
            <a href="{{action("RecordController@show", ['pid'=>$pid,'fid'=>$fid,'rid'=>$rid])}}">
                <i class="icon icon-record"></i>
                <span>{{ $pid . '-' . $fid . '-' . $rid }}</span>
            </a>
        </li>

        <li class="spacer full"></li>

        <li class="link first">
            <a href="{{action("RecordController@edit", ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">Edit Record</a>
        </li>

        <li class="link">
            <a href="{{action('RecordController@cloneRecord', ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">Duplicate Record</a>
        </li>

        <li class="link">
            {{--TODO::CASTLE--}}
{{--            <a href="{{action("RevisionController@show", ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">View Revisions ({{\App\Http\Controllers\RevisionController::getRevisionCount($rid)}})</a>--}}
            <a href="{{action("RevisionController@show", ['pid'=>$pid, 'fid'=>$fid, 'rid'=>$rid])}}">View Revisions (0)</a>
        </li>

        <li class="link">
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
</li>

@include("partials.records.modals.designateRecordPresetModal")
@include("partials.records.modals.alreadyRecordPresetModal")

<script type="text/javascript">
    makeRecordPresetURL = '{{action('RecordPresetController@presetRecord')}}';
    ridForPreset = {{$record->id}};
    csrfToken = '{{csrf_token()}}';
</script>
