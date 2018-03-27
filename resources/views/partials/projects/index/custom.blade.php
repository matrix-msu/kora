<div class="project-sort custom-projects {{ $active ? 'active' : null}} project-custom-js project-sort-js">
  @foreach($projects as $index=>$project)
    @include("partials.projects.index.project")
  @endforeach
</div>
