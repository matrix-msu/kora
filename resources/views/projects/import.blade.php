@extends('app', ['page_title' => "Import Project Setup", 'page_class' => 'project-import-setup'])

@section('leftNavLinks')
    @include('partials.menu.static', ['name' => 'Import Project Setup'])
@stop

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => true])
@stop


@section('header')
    <section class="head">
        <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-project-import"></i>
                <span>Import Project Setup</span>
            </h1>
            <p class="description">Upload your .k3proj project file below in order to import it into this kora
                intstallation. After uploading, you will supply the according project information on the next page.
                For more information and help with this process, refer to the [Importing a Project Setup - Kora Documentation].</p>
            <div class="content-sections">
              <div class="content-sections-scroll">
                <a href="#projectfile" class="projectfile-link underline-middle active">Upload Project File</a>
                <div class="tab-wrap"><span class="progression-tab"></span></div>
                <a href="#projectinfo" class="projectinfo-link">Project Information</a>
              </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    {!! Form::open(['url' => action('ImportController@importProject'),'enctype' => 'multipart/form-data', 'class' => 'form-file-input']) !!}

    <section class="projectfile-section">
        <div class="form-group">
            <label>Drag & Drop or Select the Project File Below</label>
            <input type="file" accept=".k3Proj" name="project" id="project" class="profile-input file-input-js" />
            <label for="project" class="profile-label extend">
                <p class="filename">Drag & Drop the Project File Here</p>
                <p class="instruction mb-0">
                    <span class="dd">Or Select the Project File here</span>
                    <span class="no-dd">Select a Project File here</span>
                    <span class="select-new">Select a Different Project File?</span>
                </p>
            </label>
        </div>

        <div class="form-group mt-xxxl">
            <input type="button" class="btn secondary disabled upload-file-btn-js" value="Upload Project File">
        </div>
    </section>

    <section class="projectinfo-section hidden">
        <div class="form-group">
            {!! Form::label('name', 'Project Name') !!}
            {!! Form::text('name', null, ['class' => 'text-input', 'placeholder' => 'Enter the project name here', 'autofocus']) !!}
            <p class="sub-text mt-xs">Leave blank to use name from file</p>
        </div>

        <div class="form-group mt-xl">
            {!! Form::label('slug', 'Unique Project Identifier') !!}
            {!! Form::text('slug', null, ['class' => 'text-input', 'placeholder' => "Enter the project's unique ID here (no spaces, alpha-numeric values only)"]) !!}
            <p class="sub-text mt-xs">Leave blank to use identifier from file</p>
        </div>

        <div class="form-group mt-xl">
            {!! Form::label('description', 'Description') !!}
            {!! Form::textarea('description', null, ['class' => 'text-area', 'placeholder' => "Enter the projects description here (max. 255 characters)"]) !!}
            <p class="sub-text mt-xs">Leave blank to use description from file</p>
        </div>

        <div class="form-group mt-xxxl mb-max">
            {!! Form::submit('Import Project & Information', ['class' => 'btn']) !!}
        </div>
    </section>

    {!! Form::close() !!}
@stop

@section('javascripts')
    @include('partials.projects.javascripts')

    <script type="text/javascript">
        Kora.Projects.Import();
    </script>
@stop
