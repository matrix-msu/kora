<div class="project-sort inactive-projects {{ $active ? 'active' : null}} project-sort-js">
  @foreach($inactive as $index=>$project)
    @include("partials.projects.project")
  @endforeach
</div>
