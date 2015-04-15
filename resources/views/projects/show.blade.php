@extends('app')

@section('content')
    <span><h1>{{ $project->name.' ('.$project->slug.')' }}</h1></span>
    <div>Description: {{ $project->description }}</div>
    <div>Admin: (Display Admin Here)</div>
    <hr/>
    <h2>Schemes</h2>
@stop