@extends('app', ['page_title' => "{$form->name} Association Permissions", 'page_class' => 'form-association-permissions'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'Form Associations'])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-form-associations"></i>
                <span>Form Association Permissions</span>
            </h1>
            <p class="description">This page allows you to grant association access for other forms. Associating other forms will allow them to search within this form. Select "Create a New Form Association" below, to begin creating a new form association. The newly associated form will then appear in the list below. You may also request association permission for this form to associate with other forms.</p>
            <div class="content-sections">
                <a href="#create" class="section underline-middle underline-middle-hover active">Create Form Association</a>
                <a href="#request" class="section underline-middle underline-middle-hover">Request Form Association</a>
            </div>
        </div>
    </section>
@stop

@section('body')
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>{{trans('association_index.manageassoc')}}</h3>
                    </div>
                    <div class="panel-body">
                        <div id="form_select">{{trans('association_index.forms')}}:
                            <select id="selected_assoc">
                                @foreach(\App\Form::all() as $f)
                                    @if(!in_array($f->fid,$associds) && $f->fid != $form->fid)
                                        <?php
                                        $p = \App\Http\Controllers\ProjectController::getProject($f->pid);
                                        ?>
                                        <option value="{{$f->fid}}">{{$p->name}} | {{$f->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button id="add_assoc" class="btn btn-primary">{{trans('association_index.addallowed')}}</button>
                        </div>
                        <br>
                        <div id="form_allowed">
                            <b>{{trans('association_index.allowedassoc')}}</b>
                            <hr>
                            @foreach($assocs as $a)
                                <?php
                                    $f = \App\Form::where('fid','=',$a->assocForm)->first();
                                ?>
                                <p id="form_assoc_listitem">{{$f->name}} <a class="delete_assoc" fid="{{$f->fid}}" href="javascript:void(0)">[X]</a></p>
                            @endforeach
                        </div>
                        <div>
                            <br>
                            <b>{{trans('association_index.formsassoc')}}</b>
                            <hr>
                            @foreach(\App\Http\Controllers\AssociationController::getAvailableAssociations($form->fid) as $a)
                                <?php
                                $f = \App\Form::where('fid','=',$a->dataForm)->first();
                                $p = \App\Http\Controllers\ProjectController::getProject($f->pid);
                                ?>
                                <p id="assoc_to_listitem">{{$p->name}} | {{$f->name}}</p>
                            @endforeach
                        </div>
                        {!! Form::open(['action'=>['AssociationController@requestAccess',$form->pid,$form->fid]]) !!}
                        <div id="request_select">{{trans('association_index.requestfull')}}:
                            <select id="req_avail_assoc" name="rfid">
                                @foreach(\App\Http\Controllers\AssociationController::getRequestableAssociations($form->fid) as $f)
                                    <?php
                                    $p = \App\Http\Controllers\ProjectController::getProject($f->pid);
                                    ?>
                                    <option value="{{$f->fid}}">{{$p->name}} | {{$f->name}}</option>
                                @endforeach
                            </select>
                            <button id="request_assoc" class="btn btn-primary">{{trans('association_index.request')}}</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascripts')
    @include('partials.formAssociations.javascripts')
@stop