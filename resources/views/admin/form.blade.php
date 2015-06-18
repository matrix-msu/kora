{!! Form::open(['method' => 'PATCH', 'action' => 'AdminController@update']) !!}

    <div class="form-group">
        {!! Form::label('select', 'Select user: ') !!}
        <select name="users" class="form-control">
            @foreach ($users as $user)
                <option value="{{$user->id}}">{{$user->username}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        {!! Form::label('admin', 'Should user be admin?  ') !!}
        <p style="display: inline"> No </p>{!! Form::radio('admin', 'no', true) !!}
        <p style="display: inline"> Yes </p>{!! Form::radio('admin', 'yes', false) !!}
    </div>

    <div class="form-group">
        {!! Form::label('new_password', 'New Password:') !!}
        {!! Form::text('new_password', null, ['class' => 'form-control']) !!}

        {!! Form::label('confirm', 'Confirm New Password:') !!}
        {!! Form::text('confirm', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}