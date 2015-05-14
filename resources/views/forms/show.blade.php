@extends('app')

@section('leftNavLinks')
    <li><a href="{{ url('/projects/'.$form->pid) }}">{{ $projName }}</a></li>
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>
    <div><b>Internal Name:</b> {{ $form->slug }}</div>
    <div><b>Description:</b> {{ $form->description }}</div>
    <hr/>
    <h2>Fields</h2>
    @foreach($form->fields as $field)
        <div class="panel panel-default">
            <div class="panel-heading" style="font-size: 1.5em;">
                <a href="{{ action('FieldController@show',['pid' => $field->pid,'fid' => $field->fid, 'flid' => $field->flid]) }}">{{ $field->name }}</a>
            </div>
            <div class="collapseTest" style="display:none">
                <div class="panel-body"><b>Description:</b> {{ $field->description }}</div>
                <div class="panel-footer">
                    <span>
                        <a href="{{ action('FieldController@edit',['pid' => $field->pid, 'fid' => $field->fid]) }}">[Edit]</a>
                    </span>
                </div>
            </div>
        </div>
    @endforeach

    <!-- This is where we will have the add -->
    <a href="{{action('FieldController@create', ['pid' => $form->pid, 'fid' => $form->fid]) }}">Create New Field</a>
@stop