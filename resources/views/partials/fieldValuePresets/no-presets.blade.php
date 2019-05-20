<div class="no-presets pt-xxxl">
  <div class="top pb-xxxl">
    <div class="line"></div>
    <i class="icon icon-recordPreset---Empty-State"></i>
    <div class="line"></div>
  </div>
  <div class="bottom">
    <p>No field value presets exist for this project.</p>
    @if(\Auth::user()->admin)
      <form action="{{ action('FieldValuePresetController@newPreset', ['pid' => $project->pid]) }}">
        <span class="underline-middle-hover"><input type="submit" value="Create a new field value preset"></span>
      </form>
    @endif
    <p> to get started.</p>
  </div>
</div>
