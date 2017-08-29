<div class="project-sort active-projects {{ $active ? 'active' : null}} project-sort-js">
  @foreach($projects as $index=>$project)
    @include("partials.projects.project")
  @endforeach
</div>
