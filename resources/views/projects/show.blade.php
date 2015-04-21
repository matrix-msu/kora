@extends('app')

@section('content')
    <span><h1>{{ $project->name.' ('.$project->slug.')' }}</h1></span>
    <div>Description: {{ $project->description }}</div>
    <div>Admin: (Display Admin Here)</div>
    <hr/>
    <h2>Forms</h2>
    <formObj>
        @foreach($project->forms as $form)
            <h3><a href="{{ action('FormController@show',['pid' => $project->pid, 'fid' => $form]) }}">{{ $form->name }}</a></h3>
            <div class="body">{{ $form->description }}</div>
        @endforeach
    </formObj>
    <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
        <input type="submit" value="Create New" class="btn btn-primary form-control">
    </form>
@stop