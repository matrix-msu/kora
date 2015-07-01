{!! Form::open(['method' => 'PATCH', 'action' => 'AdminController@update']) !!}

    <div class="form-group">
        {!! Form::label('select', 'Select user: ') !!}
        <select name="users" class="form-control" id="dropdown" onchange="checker()">
            @foreach ($users as $user)
                @if($user->id == 1)
                  <!-- Do nothing, we don't want to display the original admin -->
                @elseif( \Auth::user()->id == $user->id)
                    <!-- Do nothing, we don't want the current user to view their own username -->
                @else
                    <option value="{{$user->id}}" admin="{{$user->admin}}" active="{{$user->active}}">{{$user->username}}</option>
                @endif
            @endforeach
        </select>
    </div>

    <div class="form-group">
        {!! Form::label('admin', 'Admin: ') !!}
        {!! Form::checkbox('admin') !!}
    </div>

    <div class="form-group">
        {!! Form::label('active', 'Active: ') !!}
        {!! Form::checkbox('active') !!}
    </div>

    <div class="form-group">
        {!! Form::label('new_password', 'New Password:') !!}
        {!! Form::password('new_password', ['class' => 'form-control']) !!}

        {!! Form::label('confirm', 'Confirm New Password:') !!}
        {!! Form::password('confirm', ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit('Update User', ['class' => 'btn btn-primary form-control', 'name' => 'update']) !!}
    </div>

{!! Form::close() !!}

    <button onclick="deleteUser()" class="btn btn-danger form-control" name="delete">
        Delete User
    </button>




<script>
        window.onload = function() {
            var admin = $('#dropdown option:selected').attr('admin');
            var active = $('#dropdown option:selected').attr('active');

            if (admin==1)
                $('#admin').prop('checked', true);

            else
                $('#admin').prop('checked', false);

            if (active==1)
                $('#active').prop('checked', true);

            else
                $('#active').prop('checked', false);

        }

        function deleteUser(){
            var id = $('#dropdown option:selected').attr('value');
            var name = $('#dropdown option:selected').text();

            var response = confirm('Are you sure you want to delete user '+name+'?');

            if(response) {
                $.ajax({
                    url: '{{action('AdminController@deleteUser',[''])}}/'+id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function() {
                            location.reload();
                        }
                });
            }
        }

        function checker(){
            var admin = $('#dropdown option:selected').attr('admin');
            var active = $('#dropdown option:selected').attr('active');

            if (admin==1)
                $('#admin').prop('checked', true);

            else
                $('#admin').prop('checked', false);

            if (active==1)
                $('#active').prop('checked', true);

            else
                $('#active').prop('checked', false);
        }


    </script>

