<div class="form-sort active-forms {{ $active ? 'active' : null}} form-active-js form-sort-js">
  @foreach($forms as $index=>$form)
    @include("partials.projects.show.form")
  @endforeach
</div>
