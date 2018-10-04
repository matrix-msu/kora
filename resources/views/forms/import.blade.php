@extends('app', ['page_title' => "Import Form Setup", 'page_class' => 'form-import-setup'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $proj->pid])
    @include('partials.menu.static', ['name' => 'Import Form Setup'])
@stop

@section('aside-content')
  @include('partials.sideMenu.project', ['pid' => $proj->pid, 'openDrawer' => true])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-form-import"></i>
                <span>Import Form Setup</span>
            </h1>
            <p class="description">Upload your .k3form form file below in order to import it into this kora
                intstallation. After uploading, you will supply the according form information on the next page.
                For more information and help with this process, refer to the [Importing a Form Setup - Kora Documentation].</p>
            {{--TODO::Fill this link in--}}
            <div class="content-sections">
              <div class="content-sections-scroll">
                <a href="#formfile" class="formfile-link underline-middle active">Upload Form File</a>
                <div class="tab-wrap"><span class="progression-tab"></span></div>
                <a href="#forminfo" class="forminfo-link">Form Information</a>
              </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    {!! Form::open(['url' => action('ImportController@importForm', ['pid' => $proj->pid]),'enctype' => 'multipart/form-data', 'class' => 'form-file-input']) !!}

    <section class="formfile-section">
        <div class="form-group">
            <label>Drag & Drop or Select the Form File Below</label>
            <input type="file" accept=".k3Form" name="form" id="form" class="profile-input file-input-js" />
            <label for="form" class="profile-label extend">
                <p class="filename">Drag & Drop the Form File Here</p>
                <p class="instruction mb-0">
                    <span class="dd">Or Select the Form File here</span>
                    <span class="no-dd">Select a Form File here</span>
                    <span class="select-new">Select a Different Form File?</span>
                </p>
            </label>
        </div>

        <div class="form-group mt-xxxl">
            <input type="button" class="btn secondary disabled upload-file-btn-js" value="Upload Form File">
        </div>
    </section>

    <section class="forminfo-section hidden">
        <div class="form-group">
            {!! Form::label('name', 'Form Name') !!}
            {!! Form::text('name', null, ['class' => 'text-input', 'placeholder' => 'Enter the form name here', 'autofocus']) !!}
            <p class="sub-text mt-xs">Leave blank to use name from file</p>
        </div>

        <div class="form-group mt-xl">
            {!! Form::label('slug', 'Unique Form Identifier') !!}
            {!! Form::text('slug', null, ['class' => 'text-input', 'placeholder' => "Enter the form's unique ID here (no spaces, alpha-numeric values only)"]) !!}
            <p class="sub-text mt-xs">Leave blank to use identifier from file</p>
        </div>

        <div class="form-group mt-xl">
            {!! Form::label('description', 'Description') !!}
            {!! Form::textarea('description', null, ['class' => 'text-area', 'placeholder' => "Enter the form's description here (max. 255 characters)"]) !!}
            <p class="sub-text mt-xs">Leave blank to use description from file</p>
        </div>

        <div class="form-group mt-xxxl mb-max">
            {!! Form::submit('Import Form & Information', ['class' => 'btn']) !!}
            <!-- <button class="submit btn">Import Form & Information</button> -->
        </div>
    </section>

    {!! Form::close() !!}
@stop

@section('javascripts')
    @include('partials.forms.javascripts')

    <script type="text/javascript">
        Kora.Forms.Import();
    </script>
@stop
