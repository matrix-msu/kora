<div class="hidden" id="add_user_select{{$formGroup->id}}">
  @if (true)
    <div class="form-group">
      {!! Form::label("select-".$formGroup->id, 'Select User(s) to Add to Permissions Group') !!}
      <select class="multi-select" id="select-{{$formGroup->id}}"
        data-placeholder="Search and select users to be added to the permissions group    "
        data-group="{{$formGroup->id}}"
        multiple >
        @foreach($all_users as $user)
          @if(!$formGroup->hasUser($user) && \Auth::user()->id != $user->id)
            <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
          @endif
        @endforeach
      </select>
    </div>

    <div class="form-group mt-xxl add-users-submit add-users-submit-js">
      {!! Form::submit('Add User(s) to Group',['class' => 'btn']) !!}
    </div>
  @else

  @endif
</div>
