@extends('app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">

                    <div class="panel-body">

                        <h3>Manage Tokens</h3>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Search Tokens</th>
                                    <th>Projects</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach ($tokens as $token)
                                @if ($token->type == 'search')
                                <tr>
                                    <td style="vertical-align: middle;"> {{ $token->token }} </td>
                                    <td>
                                        <ul style="list-style-type: none; padding: 0;">
                                            @foreach ($token->projects()->get() as $project)
                                                <li> {{$project->name}}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <table class="table">
                            <thead>
                            <tr>
                                <th>Edit Tokens</th>
                                <th>Projects</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($tokens as $token)
                                @if ($token->type == 'edit')
                                    <tr>
                                        <td style="vertical-align: middle;"> {{ $token->token }} </td>
                                        <td>
                                            <ul style="list-style-type: none; padding: 0;">
                                                @foreach ($token->projects()->get() as $project)
                                                    <li> {{$project->name}}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <table class="table">
                            <thead>
                            <tr>
                                <th>Create Tokens</th>
                                <th>Projects</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($tokens as $token)
                                @if ($token->type == 'create')
                                    <tr>
                                        <td style="vertical-align: middle;"> {{ $token->token }} </td>
                                        <td>
                                            <ul style="list-style-type: none; padding: 0;">
                                                @foreach ($token->projects()->get() as $project)
                                                    <li> {{$project->name}}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <table class="table">
                            <thead>
                            <tr>
                                <th>Delete Tokens</th>
                                <th>Projects</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($tokens as $token)
                                @if ($token->type == 'delete')
                                    <tr>
                                        <td style="vertical-align: middle;"> {{ $token->token }} </td>
                                        <td>
                                            <ul style="list-style-type: none; padding: 0;">
                                                @foreach ($token->projects()->get() as $project)
                                                    <li> {{$project->name}}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <hr/>

                        <h3>Create Token</h3>

                        @include('partials.newToken')

                    </div>
                </div>
            </div>
        </div>
    </div>

@stop