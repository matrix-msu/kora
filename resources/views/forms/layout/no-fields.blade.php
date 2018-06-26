<div class="no-fields pt-m pb-xxl">
  <div class="top pb-xxxl">
    <div class="line"></div>
    <i class="icon icon-field-EmptyState"></i>
    <div class="line"></div>
  </div>
  <div class="bottom">
    <p>No Fields exist for this form page.</p>
    @if(\Auth::user()->canCreateFields($form))
      <form method="DET" action="{{action('FieldController@create', ['pid' => $form->pid, 'fid' => $form->fid, 'rootPage' => $page['id']]) }}">
          <input type="submit" value="Create a new field">
      </form>
    @else
      <input type="submit" value="Create a new field">
    @endif
    <p> to get started.</p>
  </div>
</div>
