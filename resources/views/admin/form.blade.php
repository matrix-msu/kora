{!! Form::open(['method' => 'PATCH', 'action' => 'AdminController@update']) !!}

    <div class="form-group">
        {!! Form::label('select', trans('admin_form.selectuser').'') !!}
        <select name="users" class="form-control" id="dropdown" onchange="checker()">
            @foreach ($users as $user)
                @if($user->id == 1)
                  <!-- Do nothing, we don't want to display the original admin -->
                @elseif( \Auth::user()->id == $user->id)
                    <!-- Do nothing, we don't want the current user to view their own username -->
                @else
                    <option picurl="{{$user->getProfilePicUrl()}}" value="{{$user->id}}" admin="{{$user->admin}}" active="{{$user->active}}">{{$user->username}}</option>
                @endif
            @endforeach
        </select>
    </div>

    <div class="form-group">
        {!! Form::label('admin', trans('admin_form.admin').'') !!}
        {!! Form::checkbox('admin') !!}
    </div>

    <div class="form-group">
        {!! Form::label('active', trans('admin_form.active').'') !!}
        {!! Form::checkbox('active') !!}
    </div>

    <div class="form-group">
        {!! Form::label('new_password', trans('admin_form.newpass').'') !!}
        {!! Form::password('new_password', ['class' => 'form-control']) !!}

        {!! Form::label('confirm', trans('admin_form.confirmpass').'') !!}
        {!! Form::password('confirm', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        @if(sizeof($users)==1)
            {!! Form::submit(trans('admin_form.updateuser'), ['class' => 'btn btn-primary form-control', 'name' => 'update', 'disabled']) !!}
        @else
            {!! Form::submit(trans('admin_form.updateuser'), ['class' => 'btn btn-primary form-control', 'name' => 'update']) !!}
        @endif
    </div>

{!! Form::close() !!}

@if(sizeof($users)==1)
    <button onclick="deleteUser()" class="btn btn-danger form-control" name="delete" disabled>
        {{trans('admin_form.deleteuser')}}
    </button>
@else
    <button onclick="deleteUser()" class="btn btn-danger form-control" name="delete">
        {{trans('admin_form.deleteuser')}}
    </button>
@endif