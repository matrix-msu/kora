@extends('app')

@section('content')
    <h1>Projects</h1>

    <hr/>

    @foreach ($projects as $project)
        <article>
            <h2>
                <a href="{{ action('ProjectController@show',[$project->id]) }}">{{ $project->name }}</a>
            </h2>

            <div class="body">{{ $project->description }}</div>
        </article>
    @endforeach

    <br/>
    
    <form action="{{ action('ProjectController@create') }}">
        <input type="submit" value="Create New" class="btn btn-primary form-control">
    </form>
@stop