<div class="no-projects pt-xxxl">
  <div class="top pb-xxxl">
    <div class="line"></div>
    <i class="icon icon-project"></i>
    <div class="line"></div>
  </div>
  <div class="bottom">
    <p>Either you don't have permissions, or no projects exist.</p>
    <form action="{{ action('ProjectController@create') }}">
      @if(\Auth::user()->admin)
        <input type="submit" value="Request permissions to a project">
        <p> or </p>
        <input type="submit" value="Create a new project">
      @endif
    </form>
    <p> to get started</p>
  </div>
</div>
