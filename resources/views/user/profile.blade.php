@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Hello {{Auth::user()->name}}, This is your profile</h1>
                    </div>

                    <div class="panel-body">
                        <h3>Your information:</h3>
                        <p>Username: {{Auth::user()->username}}</p>
                        <p>Email: {{Auth::user()->email}}</p>
                        <p>Real Name: {{Auth::user()->name}}</p>
                        <p>Organization: {{Auth::user()->organization}}</p>
                        <p>Language: {{Auth::user()->language}}</p>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection