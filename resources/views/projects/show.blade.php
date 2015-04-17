@extends('app')

@section('content')
    <span><h1>{{ $project->name.' ('.$project->slug.')' }}</h1></span>
    <div>Description: {{ $project->description }}</div>
    <div>Admin: (Display Admin Here)</div>
    <hr/>
    <h2>Forms</h2>
    <form>
        @foreach($project->forms() as $form)
            <h3>{{ $form->name }}</h3>
        @endforeach
    </form>
@stop