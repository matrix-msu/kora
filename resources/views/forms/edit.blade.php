@extends('app', ['page_title' => "Editing {$form->name}", 'page_class' => 'form-edit'])

@section('leftNavLinks')
  @include('partials.menu.project', ['pid' => $form->project_id])
  @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
  @include('partials.menu.static', ['name' => 'Edit Form'])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
@stop

@section('header')
  <section class="head">
      <a class="back" href=""><i class="icon icon-chevron"></i></a>
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
    @include("partials.forms.edit.formModals")

  <section class="edit-form">
    {!! Form::model($form,  ['method' => 'PATCH', 'action' => ['FormController@update',$form->project_id, $form->id], 'class' => 'edit-form center']) !!}
    @include('partials.forms.form',['submitButtonText' => 'Update Form', 'pid' => $form->project_id, 'type' => 'edit'])
    {!! Form::close() !!}
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.forms.javascripts')

  <script type="text/javascript">
      var validationUrl = "{{ action('FormController@validateFormFields', ["pid" => $form->project_id, "fid" =>$form->id]) }}";
      var csrfToken = "{{ csrf_token() }}";

      Kora.Forms.Edit();
  </script>
@stop
