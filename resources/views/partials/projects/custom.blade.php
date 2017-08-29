<div class="project-sort custom-projects {{ $active ? 'active' : null}} project-custom-js project-sort-js">
  @foreach($custom as $index=>$project)
    @include("partials.projects.project")
  @endforeach
</div>
