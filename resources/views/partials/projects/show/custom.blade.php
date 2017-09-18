<div class="form-sort custom-forms {{ $active ? 'active' : null}} form-custom-js form-sort-js">
  @foreach($custom as $index=>$form)
    @include("partials.projects.show.form")
  @endforeach
</div>
