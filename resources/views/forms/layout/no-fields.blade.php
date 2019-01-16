@php $count = count($page['flids']); @endphp
<div class="no-fields pt-m pb-xxl {{ $count == 0 ? null : 'hidden' }}">
  <div class="top pb-xxxl">
    <div class="line"></div>
    <i class="icon icon-field-EmptyState"></i>
    <div class="line"></div>
  </div>
  <div class="bottom">
    <p>No Fields exist for this form page.</p>
    @if(\Auth::user()->canCreateFields($form))
      <form method="DET" action="{{action('FieldController@create', ['pid' => $form->project_id, 'fid' => $form->id, 'rootPage' => $idx]) }}">
          <span class="underline-middle-hover"><input type="submit" value="Create a new field"></span>
      </form>
    @else
      <span class="underline-middle-hover"><input type="submit" value="Create a new field"></span>
    @endif
    <p> to get started.</p>
  </div>
</div>
