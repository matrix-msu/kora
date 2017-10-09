<div class="hidden" id="add_user_select{{$projectGroup->id}}">
  @if (true)
    <div class="form-group">
      {!! Form::label("select{{$projectGroup->id}}", 'Select User(s) to Add to Permissions Group') !!}
      <select class="multi-select" id="select{{$projectGroup->id}}"
        data-placeholder="Search and select users to be added to the permissions group    "
        data-group="{{$projectGroup->id}}"
        multiple >
        @foreach($all_users as $user)
          @if(!$projectGroup->hasUser($user) && \Auth::user()->id != $user->id)
            <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
          @endif
        @endforeach
      </select>
    </div>

    <div class="form-group add-users-submit add-users-submit-js">
      {!! Form::submit('Add User(s) to Permission Group',['class' => 'btn']) !!}
    </div>
  @else
    <div class="request-permissions-error">
      <p class="icon-container">
        <!--  Leave this as a long string -->
        <i class="icon icon-project-happy"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
      </p>
      <p class="text">You already have access to everything!</p>
    </div>

    <div class="form-group submit">
      <a class="btn modal-toggle-js" href="#">Great!</a>
    <div>
  @endif
</div>
