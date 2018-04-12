@extends('app', ['page_title' => "K2 Scheme Importer", 'page_class' => 'scheme-import-setup'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $proj->pid])
    @include('partials.menu.static', ['name' => 'K2 Scheme Importer'])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-form-scheme-importer"></i>
                <span>Kora 3 Scheme Importer</span>
            </h1>
            <p class="description">Here you can import a scheme and its record set from Kora 2. Obtain the XMLs from
                kora 2, by exporting the scheme, and exporting the scheme’s record set. Upload the scheme XML here,
                followed by the record set XML. Note: A scheme XML must be added in order to import the record XML and
                record files. Associated controls and record values are not compatible with the scheme importer. They
                can only be maintained by a full K2 System Transfer (Kora Exodus).</p>
            <div class="content-sections">
                <a href="#formfile" class="formfile-link underline-middle active">Upload Form File</a>
                <a href="#forminfo" class="forminfo-link">Form Information</a>
            </div>
        </div>
    </section>
@stop

@section('body')
    {!! Form::open(['url' => action('ImportController@importFormK2', ['pid' => $proj->pid]),'enctype' => 'multipart/form-data', 'id' => 'k2_form']) !!}

    <section class="formfile-section">
        <div class="form-group">
            <label>Drag & Drop or Select the Kora 2 Scheme XML Below</label>
            <input type="file" accept=".xml" name="form" id="form" class="scheme-input profile-input file-input-js" />
            <label for="form" class="scheme-label profile-label">
                <p class="scheme-filename filename">Drag & Drop the Kora 2 Scheme XML Here</p>
                <p class="scheme-instruction instruction mb-0">
                    <span class="dd">Or Select the Kora 2 Scheme XML here</span>
                    <span class="no-dd">Select a Kora 2 Scheme XML here</span>
                    <span class="select-new">Select a Different Kora 2 Scheme XML?</span>
                </p>
            </label>
        </div>

        <div class="form-group mt-xxxl" id="scroll-here">
            <div class="spacer"></div>
        </div>

        <section class="formfile-section-2 hidden">
            <div class="form-group mt-xxxl">
                <div class="scheme-record-title">Add the Record XML and Record files that correlate with the Scheme XML above.</div>
            </div>

            <div class="form-group mt-xxxl">
                <label>Drag & Drop or Select the Kora 2 Record XML Below</label>
                <input type="file" accept=".xml" name="records" id="records" class="record-input profile-input" />
                <label for="records" class="record-label profile-label">
                    <p class="record-filename filename">Drag & Drop the Kora 2 Record XML Here</p>
                    <p class="record-instruction instruction mb-0">
                        <span class="dd">Or Select the Kora 2 Record XML here</span>
                        <span class="no-dd">Select a Kora 2 Record XML here</span>
                        <span class="select-new">Select a Different Kora 2 Record XML?</span>
                    </p>
                </label>
            </div>

            <div class="form-group mt-xxxl">
                <label>Drag & Drop or Select the Kora 2 Record Files Zip Below</label>
                <input type="file" accept=".xml" name="files" id="files" class="file-input profile-input" />
                <label for="files" class="file-label profile-label">
                    <p class="file-filename filename">Drag & Drop the Kora 2 Record Files Zip Here</p>
                    <p class="file-instruction instruction mb-0">
                        <span class="dd">Or Select the Kora 2 Record Files Zip here</span>
                        <span class="no-dd">Select a Kora 2 Record Files Zip here</span>
                        <span class="select-new">Select a Different Kora 2 Record Files Zip?</span>
                    </p>
                </label>
            </div>

            <div class="form-group scheme-import-button">
                <input type="button" class="btn scheme-import-submit secondary pre-fixed-js upload-file-btn-js" value="Upload Form File">
            </div>
        </section>
    </section>

    <section class="forminfo-section hidden" id="top-dog">
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
        </div>
    </section>

    {!! Form::close() !!}
@stop

@section('javascripts')
    @include('partials.forms.javascripts')

    <script type="text/javascript">
        Kora.Forms.ImportK2();
    </script>
@stop

@section('content')
    {!! Form::open(['url' => action('ImportController@importFormK2', ['pid' => $proj->pid]),'enctype' => 'multipart/form-data', 'id' => 'k2_form']) !!}
    <div class="form-group" style="background-color:#7A96BD">
        {!! Form::label('form', trans('forms_importk2.schemexml').': ') !!}
        {!! Form::file('form', ['class' => 'form-control', 'accept' => '.xml']) !!}
    </div>

    <div class="form-group" style="background-color:#7A96BD">
        {!! Form::label('records', trans('forms_importk2.recordxml').': ') !!}
        {!! Form::file('records', ['class' => 'form-control', 'accept' => '.xml']) !!}
    </div>

    <div class="form-group" style="background-color:#7A96BD">
        {!! Form::label('files', trans('forms_importk2.filezip').': ') !!}
        {!! Form::file('files', ['class' => 'form-control', 'accept' => '.zip']) !!}
    </div>

    <div class="form-group" id="k2_submit">
        <button class="form-control btn btn-primary">{{trans('forms_importk2.importsubmit')}}</button>
    </div>

    <div style="display:none;" id="search_progress" class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
            {{trans('update_index.loading')}}
        </div>
    </div>

    {!! Form::close() !!}
@stop