<div class="modal modal-js modal-mask new-permission-modal new-permission-modal-js">
  <div class="content">
    <div class="header">
      <span class="title">Create a New Permissions Group</span>
      <a href="#" class="modal-toggle modal-toggle-js">
        <i class="icon icon-cancel"></i>
      </a>
    </div>
    <div class="body">
      {!! Form::open(['method' => 'POST', 'action' => ['FormGroupController@create', $project->id, $form->id]]) !!}
        <div class="form-group">
          {!! Form::label('name', 'Permissions Group Name') !!}
		  <span class="error-message"></span>
          {!! Form::text('name', null, ['class' => 'text-input create-group-name-js', 'placeholder' => "Enter the name of the permissions group here"]) !!}
        </div>

		<div class="form-group">
          <div class="actions">
            <div class="form-group action">
              <div class="action-column">
                <div class="check-box-half check-box-rectangle">
                  <input type="checkbox"
                        value="1"
                        class="check-box-input preset-input-js"
                        name="create" />
                  <span class="check"></span>
                  <span class="placeholder">Can Create Forms</span>
                </div>
          
                <div class="check-box-half check-box-rectangle">
                  <input type="checkbox"
                        value="1"
                        class="check-box-input preset-input-js"
                        name="ingest" />
                  <span class="check"></span>
                  <span class="placeholder">Can Create Records</span>
                </div>
              </div>
            </div>
          
            <div class="form-group action">
              <div class="action-column">
                <div class="check-box-half check-box-rectangle">
                  <input type="checkbox"
                        value="1"
                        class="check-box-input preset-input-js"
                        name="edit" />
                  <span class="check"></span>
                  <span class="placeholder">Can Edit Forms</span>
                </div>
          
                <div class="check-box-half check-box-rectangle">
                  <input type="checkbox"
                        value="1"
                        class="check-box-input preset-input-js"
                        name="modify" />
                  <span class="check"></span>
                  <span class="placeholder">Can Edit Records</span>
                </div>
              </div>
            </div>
          
            <div class="form-group action">
              <div class="action-column">
                <div class="check-box-half check-box-rectangle">
                  <input type="checkbox"
                        value="1"
                        class="check-box-input preset-input-js"
                        name="delete" />
                  <span class="check"></span>
                  <span class="placeholder">Can Delete Forms</span>
                </div>
                
                <div class="check-box-half check-box-rectangle">
                  <input type="checkbox"
                        value="1"
                        class="check-box-input preset-input-js"
                        name="destroy" />
                  <span class="check"></span>
                  <span class="placeholder">Can Delete Records</span>
                </div>
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
              <option value="{{$user->id}}">{{$user->getFullName()}} ({{$user->username}})</option>
            @endforeach
          </select>
        </div>

        <div class="form-group mt-xxl create-submit-js">
          {!! Form::submit('Create New Permissions Group',['class' => 'btn']) !!}
        </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>
