@extends('app', ['page_title' => "Import Records", 'page_class' => 'record-import-setup'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['fid' => $form->id])
    @include('partials.menu.static', ['name' => 'Import Records'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-record-import"></i>
                <span class="header-text-js">Import Records</span>
            </h1>
            <p class="description desc-text-js">
                You can import records via a CSV, JSON, or XML File. Please read the Record Import Documentation to
                learn about the structure of records for each file type. This systems also allows records to be
                associated between records in the uploaded forms, and to be associated by existing records. Please see
                the Kora Documentation for references on how to define these associations.
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
        <div class="form-group">
            <label>Drag & Drop or Select the XML / JSON / CSV File Below</label>
            <input type="file" accept=".xml,.json,.csv" name="records" id="records" class="record-input profile-input record-input-js" />
            <label for="records" class="record-label profile-label extend">
                <p class="record-filename filename">Drag & Drop the XML / JSON / CSV File Here</p>
                <p class="record-instruction instruction mb-0">
                    <span class="dd">Or Select the XML / JSON / CSV File here</span>
                    <span class="no-dd">Select a XML / JSON / CSV File here</span>
                    <span class="select-new">Select a Different XML / JSON / CSV File?</span>
                </p>
            </label>
        </div>

        <div class="form-group mt-xxxl spacer-fade-js hidden" id="scroll-here">
            <div class="spacer"></div>
        </div>

        <section class="record-import-section-2 hidden">
            <div class="form-group mt-xxxl">
                <div class="record-file-title">If you have files that correlate to the XML / JSON / CSV File above, upload
                    them below in a zipped file. If the zipped file is too large, extract the files manually to
                    'storage/app/tmpFiles/impU{{\Auth::user()->id}}/'</div>
            </div>

            <div class="form-group mt-xxxl">
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

            <div class="form-group record-import-button">
                <input type="button" class="btn record-import-submit pre-fixed-js upload-record-btn-js" value="Upload Record Import File">
            </div>
        </section>
    </section>

    <section class="recordmatch-section hidden">

    </section>

    <section class="recordresults-section hidden">
        <div class="form-group">
            <div class="progress-bar-custom">
                <span class="progress-bar-filler progress-fill-js"></span>
            </div>

            <p class="progress-bar-text progress-text-js">0 of 1000 Records Submitted</p>
        </div>
    </section>

    <section class="allrecords-section hidden">
        <div class="form-group">
            <div class="records-imported-label records-imported-label-js">N of X Records Succesfully Imported!</div>

            <p class="records-imported-text-js mt-m"></p>
        </div>

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

        <div class="form-group records-imported-text3-js"></div>

        <div class="form-group mt-xxl">
            <div class="form-quick-options">
                <div class="button-container button-container3-js">
                </div>
            </div>
        </div>
    </section>
@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script type="text/javascript">
        var fidForFormData = '{{$form->id}}';
        var matchUpFieldsUrl = '{{ action('ImportController@matchupFields',['pid'=>$form->project_id,'fid'=>$form->id])}}';
        var importRecordUrl = '{{ action('ImportController@importRecord',['pid'=>$form->project_id,'fid'=>$form->id]) }}';
        var connectRecordsUrl = '{{ action('ImportController@connectRecords',['pid'=>$form->project_id,'fid'=>$form->id]) }}';
        var viewRecordsUrl = '{{ action('RecordController@index',['pid' => $form->project_id, 'fid' => $form->id]) }}';
        var downloadFailedUrl = '{{ action('ImportController@downloadFailedRecords',['pid'=>$form->project_id,'fid'=>$form->id]) }}';
        var downloadReasonsUrl = '{{ action('ImportController@downloadFailedReasons',['pid'=>$form->project_id,'fid'=>$form->id]) }}';
        var downloadConnectionUrl = '{{ action('ImportController@downloadFailedConnections',['pid'=>$form->project_id,'fid'=>$form->id]) }}';
        var CSRFToken = '{{ csrf_token() }}';

        Kora.Records.Import();
    </script>
@stop
