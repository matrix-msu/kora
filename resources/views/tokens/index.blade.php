@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>{{trans('tokens_index.manage')}}</h3>
                    </div>
                    <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                        <tr style="border-bottom: 2px solid #ddd">
                            <th>{{trans('tokens_index.tokens')}}</th>
                            <th class="pull-right" style="border-bottom: 0px">{{trans('tokens_index.projects')}}</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($tokens as $token)
                            <tr>
                                <td>
                                    {{$token->title}}: {{$token->token}} <a onclick="deleteToken({{$token->id}})" href="javascript:void(0)">[{{trans('tokens_index.delete')}}]</a>
                                    <br>
                                    Permissions:
                                    @if($token->search)<b>Search</b>@else<i>search</i>@endif
                                    @if($token->create)<b>Create</b>@else<i>create</i>@endif
                                    @if($token->edit)<b>Edit</b>@else<i>edit</i>@endif
                                    @if($token->delete)<b>Delete</b>@else<i>delete</i>@endif
                                </td>
                                <td>
                                    <ul class="pull-right" style="list-style-type: none; padding: 0;">
                                        @foreach ($token->projects()->get() as $project)
                                            <li>
                                                {{$project->name}} <a onclick="deleteProject({{$project->pid}}, {{$token->id}})" href="javascript:void(0)">[X]</a>
                                            </li>
                                        @endforeach

                                        <li>
                                            <select onchange="addProject({{$token->id}})" id="dropdown{{$token->id}}">
                                                <option selected disabled>{{trans('tokens_index.add')}}</option>
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
                        @endforeach
                        </tbody>
                    </table>

                        <hr/>

                        <h3>{{trans('tokens_index.create')}}</h3>

                        @include('partials.newToken')

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>
        /**
         * Removes the relationship between a certain token and a project.
         *
         * @param pid {int} The project id.
         * @param token {int} The token id.
         */
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

        /**
         * Adds a relationship between a token and a project.
         *
         * @param id {int} The project id.
         */
        function addProject(id) {
            var selector = $('#dropdown' +id+ ' option:selected');

            var pid = selector.attr('id');
            var token = selector.attr('token');

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

        /**
         * Deletes a particular token.
         * Prompts the user before doing so with a simple confirm window.
         *
         * @param id {int} The token id.
         */
        function deleteToken(id) {
            var encode = $('<div/>').html("{{trans('tokens_index.areyousure')}}").text();
            var response = confirm(encode + '?');
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