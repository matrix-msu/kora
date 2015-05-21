@extends('app')

@section('leftNavLinks')
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $proj->name }}<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="{{ url('/projects/'.$field->pid) }}">Project Home</a></li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $form->name }}<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="{{ url('/projects/'.$field->pid).'/forms/'.$field->fid}}">Form Home</a></li>
        </ul>
    </li>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $field->name }}<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="{{ url('/projects/'.$field->pid.'/forms/'.$field->fid.'/fields/'.$field->flid) }}">Field Home</a></li>
            <li><a href="{{ url('/projects/'.$field->pid.'/forms/'.$field->fid.'/fields/'.$field->flid.'/options') }}">Field Options</a></li>
        </ul>
    </li>
@stop

@section('content')
    <span><h1>{{ $field->name }}</h1></span>
    <div><b>Slug:</b> {{ $field->slug }}</div>
    <div><b>Type:</b> {{ $field->type }}</div>
    <div><b>Description:</b> {{ $field->description }}</div>
    <hr/>

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@update',$field->pid, $field->fid]]) !!}
    <div class="input-group">
        <span class="input-group-addon" id="basic-addon1">Order:</span>
        <input type="text" class="form-control" placeholder="{{ $field->order }}" aria-describedby="basic-addon1">
        <span class="input-group-addon" id="basic-addon1submit"><a href="#">Update Order</a></span>
    </div>
    <br>
    <div class="input-group">
        <span class="input-group-addon" id="basic-addon2">Required:</span>
        <input type="text" class="form-control" placeholder="{{ $field->required }}" aria-describedby="basic-addon2">
        <span class="input-group-addon" id="basic-addon2submit"><a href="#">Update Required</a></span>
    </div>
    <br>
    <div class="input-group">
        <span class="input-group-addon" id="basic-addon3">Default:</span>
        <input type="text" class="form-control" placeholder="{{ $field->default }}" aria-describedby="basic-addon3">
        <span class="input-group-addon" id="basic-addon3submit"><a href="#">Update Default</a></span>
    </div>
    {!! Form::close() !!}


@stop