@extends('app', ['page_title' => "Import Multi Form Records", 'page_class' => 'multi-import-setup'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->id])
    @include('partials.menu.static', ['name' => 'Import Multi Form Records'])
@stop

@section('aside-content')
    @include('partials.sideMenu.project', ['pid' => $project->id, 'openDrawer' => true])
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-record-import"></i>
                <span class="header-text-js">Import Multi Form Records</span>
            </h1>
            <p class="description desc-text-js">You can import records for multiple Forms via XML or JSON Files. Upload
                one file for each Form, and then enter the forms in order in the list below. Compared to the records
                import page, there is no matchup sequence so file field names must match the expected Unique Field
                Identifiers. This systems also allows records to be associated between the uploaded forms. Please see
                the Kora 3 Documentation for references on how to define cross-Form associations.</p>
            <div class="content-sections sections-remove-js">
              <div class="content-sections-scroll">
                <a href="#recordfile" class="recordfile-link underline-middle active">Upload Record Files</a>
                <div class="tab-wrap"><span class="progression-tab"></span></div>
                <a href="#recordmatch" class="recordmatch-link">Field Matching</a>
              </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    <section class="recordfile-section">
        <div class="form-group mt-xxxl">
            <label>Record XML / JSON Files</label>
            <span class="error-message"></span>
        </div>

        <section class="filenames filenames-js">

        </section>

        <div class="form-group progress-bar-div">
            <div class="file-upload-progress progress-bar-js"></div>
        </div>

        <form>
            @csrf
            <div class="form-group new-object-button low-margin">
                <input type="button" class="kora-file-button-js" value="Add New File">
                <input type="file" name="file0[]" id="records" class="kora-file-upload-js hidden"
                       data-url="{{ url('saveTmpFileMF') }}"
                       multiple accept=".xml,.json,.csv">
            </div>

            <div class="form-group mt-xl">
                <label>Select Forms (in order of files above)</label>
                <span class="error-message"></span>
                {!! Form::select('importForms[]',$forms, null, ['class' => 'multi-select modify-select import-form-js', 'multiple']) !!}
            </div>

            <div class="form-group mt-xxxl spacer-fade-js hidden" id="scroll-here">
                <div class="spacer"></div>
            </div>

            <div class="form-group mt-xxxl">
                <div class="record-file-title">If you have files that correlate to the XML / JSON File above, upload
                    them below in a zipped file. If the zipped file is too large, extract the files manually to
                    'storage/app/tmpFiles/impU{{\Auth::user()->id}}/'</div>
            </div>

            <div class="form-group mt-xl">
                <label>Drag & Drop or Select the Zipped File Below</label>
                <input type="file" accept=".zip" name="files" id="files" class="file-input profile-input file-input-js" />
                <label for="files" class="file-label profile-label extend">
                    <p class="file-filename filename">Drag & Drop the Zipped File Here</p>
                    <p class="file-instruction instruction mb-0">
                        <span class="dd">Or Select the Zipped File here</span>
                        <span class="no-dd">Select a Zipped File here</span>
                        <span class="select-new">Select a Different Zipped File?</span>
                    </p>
                </label>
            </div>

            <div class="form-group record-import-button sections-remove-js mt-xxxl">
                <input type="button" class="btn upload-record-btn-js" value="Upload Record Import Files">
            </div>
        </form>
    </section>

    <section class="recordmatch-section hidden">

    </section>

    <section class="recordresults-section hidden">
        <div class="form-group">
            <div class="progress-bar-custom">
                <span class="progress-bar-filler progress-fill-js"></span>
            </div>

            <p class="progress-bar-text progress-text-js">0 of 1000 Records Submitted</p>

            <div class="form-group mt-xxl">
                <div class="form-quick-options">
                    <div class="button-container button-container-js">
                    </div>
                </div>
            </div>

            <div class="form-group records-imported-text2-js"></div>

            <div class="form-group mt-xxl">
                <div class="form-quick-options">
                    <div class="button-container button-container2-js">
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script type="text/javascript">
        var CSRFToken = '{{ csrf_token() }}';
        var deleteFileUrl = '{{ url('deleteTmpFileMF') }}/';
        var mfrInputURL = '{{ url('projects/'.$project->id.'/importMF') }}';
        var importRecordUrl = '{{ url('projects/'.$project->id.'/importMFRecord') }}';
        var crossAssocURL = '{{ url('projects/'.$project->id.'/importMFAssoc') }}';
        var downloadFailedUrl = '{{ action('ImportMultiFormController@downloadFailedRecords',['pid'=>$project->id]) }}';
        var downloadReasonsUrl = '{{ action('ImportMultiFormController@downloadFailedReasons',['pid'=>$project->id]) }}';

        Kora.Records.ImportMF();
    </script>
@stop
