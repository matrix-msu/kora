@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <h3>Manage Tokens</h3>

                        @foreach(array('Search', 'Edit', 'Create', 'Delete') as $type)
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>{{$type}} Tokens</th>
                                    <th>Projects</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach ($tokens as $token)
                                    @if ($token->type == strtolower($type))
                                        <tr>
                                            <td>{{$token->token}} <a onclick="deleteToken({{$token->id}})" href="javascript:void(0)">[Delete]</a></td>
                                            <td>
                                                <ul style="list-style-type: none; padding: 0;">
                                                    @foreach ($token->projects()->get() as $project)
                                                        <li>
                                                            {{$project->name}} <a onclick="deleteProject({{$project->pid}}, {{$token->id}})" href="javascript:void(0)">[X]</a>
                                                        </li>
                                                    @endforeach

                                                    <li>
                                                        <select onchange="addProject({{$token->id}})" id="dropdown{{$token->id}}">
                                                            <option selected disabled>Add a project</option>
                                                            @foreach ($all_projects as $project)
                                                                @if($token->hasProject($project))
                                                                @else
                                                                    <option id="{{$project->pid}}" token="{{$token->id}}">
                                                                        {{$project->name}}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        @endforeach

                        <hr/>

                        <h3>Create Token</h3>

                        @include('partials.newToken')

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>
        function deleteProject(pid, token) {
            $.ajax({
                //We manually create the link in a cheap way because the JS isn't aware of the pid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('TokenController@deleteProject',['']) }}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "pid": pid,
                    "token": token
                },
                success: function(){
                    location.reload();
                }
            });
        }

        function addProject(id) {
            var pid = $('#dropdown' +id+ ' option:selected').attr('id');
            var token = $('#dropdown' +id+ ' option:selected').attr('token');

                    $.ajax({
                //Same method as deleteProject
                url: '{{ action('TokenController@addProject')}}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "pid": pid,
                    "token": token
                },
                success: function(){
                    location.reload();
                }
            });
        }

        function deleteToken(id) {
            var response = confirm('Are you sure you want to delete this token?');
            if (response) {
                $.ajax({
                    url: '{{ action('TokenController@deleteToken',['']) }}',
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id": id
                    },
                    success: function(){
                        location.reload();
                    }
                });
            }
        }

         $('#projects').select2();
    </script>
@stop