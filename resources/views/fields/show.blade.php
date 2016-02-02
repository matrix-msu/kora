@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $field->pid])
    @include('partials.menu.form', ['pid' => $field->pid, 'fid' => $field->fid])
    @include('partials.menu.options', ['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid])
@stop

@section('content')
    <span><h1>{{ $field->name }}</h1></span>
    <div><b>{{trans('fields_show.name')}}:</b> {{ $field->slug }}</div>
    <div><b>{{trans('fields_show.type')}}:</b> {{ $field->type }}</div>
    <div><b>{{trans('fields_show.desc')}}:</b> {{ $field->desc }}</div>
    <hr/>

    @yield('fieldOptions')
@stop