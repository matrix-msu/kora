<div class="modal modal-js modal-mask edit-group-name-modal-js">
  <div class="content">
    <div class="header">
      <span class="title">Edit Group Name</span>
      <a href="#" class="modal-toggle modal-toggle-js">
        <i class="icon icon-cancel"></i>
      </a>
    </div>
    <div class="body">
      <div class="form-group">
        {!! Form::label('name', 'Permissions Group Name') !!}
        {!! Form::text('name', null, ['class' => 'text-input group-name-js', 'placeholder' => "Enter the new permissions group's name"]) !!}
      </div>

      <div class="form-group mt-xxl add-users-submit edit-group-name-submit-js">
        {!! Form::submit('Update Group Name',['class' => 'btn edit-group-name-button-js']) !!}
      </div>
    </div>
  </div>
</div>
