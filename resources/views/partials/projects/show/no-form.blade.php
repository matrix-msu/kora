<div class="no-forms pt-xxxl">
  <div class="top pb-xxxl">
    <div class="line"></div>
    <i class="icon icon-form"></i>
    <div class="line"></div>
  </div>
  <div class="bottom">
    <p>No forms exist.</p>
    @if(\Auth::user()->canCreateForms($project))
      <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
          <p><a class="underline-middle-hover pb-xxs">Create new forms</a> to get started</p>
      </form>
    @endif
  </div>
</div>
