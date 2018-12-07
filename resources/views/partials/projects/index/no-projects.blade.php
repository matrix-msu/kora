<div class="no-projects center">
  <div class="top pb-xxxl">
    <div class="line"></div>
    <i class="icon icon-empty-projects"></i>
    <div class="line"></div>
  </div>
  <div class="bottom">
    @if(Auth::user()->admin)
    <p class="part-opacity">No projects exist.</p>
    <p><a class="underline-middle-hover pb-xxs kora-green" href="{{ action('ProjectController@create') }}">Create a new project</a> to get started.</p>
	@else
	<p class="part-opacity">Either you don't have permissions, or no projects exist.</p>
	<p><a class="underline-middle-hover pb-xxs kora-green project-request-perms-js">Request permissions to a project</a> to get started.</p>
	@endif
  </div>
</div>