<div class="no-forms pt-xxxl">
  <div class="top pb-xxxl">
    <div class="line"></div>
    <i class="icon icon-form-EmptyState"></i>
    <div class="line"></div>
  </div>
  <div class="bottom">
    <p>No forms exist for this project.</p>
    @if(\Auth::user()->canCreateForms($project))
      <form action="{{ action('FormController@create', ['pid' => $project->id]) }}">
          <span class="underline-middle-hover"><input type="submit" value="Create a new form"></span><p> to get started</p>
      </form>
    @endif
  </div>
</div>
