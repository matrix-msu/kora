@extends('app', ['page_title' => "Editing {$form->name}", 'page_class' => 'form-edit'])

@section('leftNavLinks')
  @include('partials.menu.project', ['pid' => $form->pid])
  @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
  @include('partials.menu.static', ['name' => 'Edit Form'])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid, 'openDrawer' => true])
@stop

@section('header')
  <section class="head">
      <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-form-edit"></i>
          <span>Edit Form</span>
        </h1>
        <p class="description">Edit the form information below, and then select “Update Form”</p>
      </div>
  </section>
@stop

@section('body')
  <section class="edit-form center">
    {!! Form::model($form,  ['method' => 'PATCH', 'action' => ['FormController@update',$form->pid, $form->fid], 'class' => 'edit-form']) !!}
    @include('partials.forms.form',['submitButtonText' => 'Update Form', 'pid' => $form->pid, 'type' => 'edit'])
    {!! Form::close() !!}

    <div class="modal modal-js modal-mask form-cleanup-modal-js">
      <div class="content small">
        <div class="header">
          <span class="title title-js"></span>
          <a href="#" class="modal-toggle modal-toggle-js">
            <i class="icon icon-cancel"></i>
          </a>
        </div>
        <div class="body">
          @include("partials.forms.edit.formDeleteForm")
        </div>
      </div>
    </div>
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.forms.javascripts')

  <script type="text/javascript">
      var validationUrl = "{{ action('FormController@validateFormFields', ["pid" => $form->pid, "fid" =>$form->fid]) }}";
      var csrfToken = "{{ csrf_token() }}";

      Kora.Forms.Edit();
  </script>
@stop
