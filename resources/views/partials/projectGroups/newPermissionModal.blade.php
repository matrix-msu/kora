<div class="modal modal-js modal-mask new-permission-modal new-permission-modal-js">
  <div class="content">
    <div class="header">
      <span class="title">Create a New Permissions Group</span>
      <a href="#" class="modal-toggle modal-toggle-js">
        <i class="icon icon-cancel"></i>
      </a>
    </div>
    <div class="body">
      {!! Form::open(['method' => 'POST', 'action' => ['ProjectGroupController@create', $project->id]]) !!}
        <div class="form-group">
          {!! Form::label('name', 'Permissions Group Name') !!}
		  <span class="error-message"></span>
          {!! Form::text('name', null, ['class' => 'text-input create-group-name-js', 'placeholder' => "Enter the name of the permissions group here"]) !!}
        </div>

		<div class="form-group">
          <div class="actions">
            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                       value="1"
                       class="check-box-input preset-input-js"
                       name="create" />
                <span class="check"></span>
                <span class="placeholder">Can Create Forms</span>
              </div>
            </div>
          
            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                       value="1"
                       class="check-box-input preset-input-js"
                       name="edit" />
                <span class="check"></span>
                <span class="placeholder">Can Edit Forms</span>
              </div>
            </div>
          
            <div class="form-group action">
              <div class="check-box-half check-box-rectangle">
                <input type="checkbox"
                       value="1"
                       class="check-box-input preset-input-js"
                       name="delete" />
                <span class="check"></span>
                <span class="placeholder">Can Delete Forms</span>
              </div>
            </div>
		    <span class="error-message group-options-error-message"></span>
          </div>
		</div>
		  
        <div class="form-group users-select">
          {!! Form::label("users", 'Select User(s) in Permissions Group') !!}
          <select class="multi-select" id="users" name="users[]"
            data-placeholder="Search and select users to be added to the permissions group    "
            multiple >
            @foreach($all_users as $user)
              @if ($user->id !== 1)
                <option value="{{$user->id}}">{{$user->getFullName()}} ({{$user->username}})</option>
              @endif
            @endforeach
          </select>
        </div>
		
		<div class="form-group mt-xxl">
		  <label for="emails">Not Listed Above? Enter Email(s) to Create Users</label>
		  <span class="error-message"></span>
		  <input type="text" class="text-input" id="emails-new-perm-group" name="emails" placeholder="Enter user email(s) here. Separate multiple emails with a space or a comma.">
		</div>
		
        <div class="form-group mt-xxl create-submit-js">
          {!! Form::submit('Create New Permissions Group',['class' => 'btn']) !!}
        </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>
