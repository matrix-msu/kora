<div class="hidden" id="add_user_select{{$projectGroup->id}}">
  @if (true)
    <div class="form-group">
      {!! Form::label("select-".$projectGroup->id, 'Select User(s) to Add to Permissions Group') !!}
      <select class="multi-select" id="select-{{$projectGroup->id}}"
        data-placeholder="Search and select users to be added to the permissions group    "
        data-group="{{$projectGroup->id}}"
        multiple >
        @foreach($all_users as $user)
          @if(!$projectGroup->hasUser($user) && \Auth::user()->id != $user->id)
            <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}} ({{ $user->username }})</option>
          @endif
        @endforeach
      </select>
    </div>
	
	<div class="form-group mt-xxl add-users-email-js">
	  <label for="emails">Not Listed Above? Invite Users Via Email</label>
    <span class="error-message emails"></span>
	  <input type="text" class="text-input emails" id="emails-{{$projectGroup->id}}" name="emails" placeholder="Enter invitee email(s) here. Seperate multiple emails with a space or a comma.">
	</div>
	
	<div class="form-group mt-xxl">
	  <label for="message">Include a Personal Message?</label>
	  <textarea class="text-area" id="message-{{$projectGroup->id}}" name="message" placeholder="Provide further details to be sent to added and invited users. Including a personal message is optional."></textarea>
	</div>

    <div class="form-group mt-xxl add-users-submit add-users-submit-js">
      {!! Form::submit('Add User(s) to Permissions Group',['class' => 'btn']) !!}
    </div>
  @endif
</div>
