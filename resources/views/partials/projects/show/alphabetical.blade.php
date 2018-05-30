<div class="form-sort active-forms {{ $active ? 'active' : null}} form-active-js form-sort-js">
  @if (count($forms) > 0)
    @foreach($forms as $index=>$form)
      @include("partials.projects.show.form")
    @endforeach
  @else
    @include("partials.projects.show.no-form")
  @endif
</div>
