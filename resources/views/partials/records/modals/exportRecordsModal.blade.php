<div class="modal modal-js modal-mask export-records-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title export-records-title-js">Export All Records</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body exp-rec">
            <div class="form-group export-files-desc-js">
                Export all form records in the formats of JSON or XML. You may also export all record files as a zip.
            </div>
            <div class="form-group mt-m">
                <a href="#" class="btn export-begin-files-js" token="{{ csrf_token() }}"
                   startURL="{{ action('ExportController@prepRecordFiles',['pid' => $form->project_id, 'fid' => $form->id]) }}"
                   checkURL="{{ action('ExportController@checkRecordFiles',['pid' => $form->project_id, 'fid' => $form->id]) }}"
                   endURL="{{ action('ExportController@exportRecordFiles',['pid' => $form->project_id, 'fid' => $form->id, 'name' => '']) }}"
                >Export Record Files</a>
            </div>
            <div class="form-group mt-m">
                <a href="{{action('ExportController@exportRecords', ['pid' => $pid, 'fid' => $fid, 'type' => 'JSON'])}}" class="btn">Export JSON</a>
            </div>
            <div class="form-group mt-m">
                <a href="{{action('ExportController@exportRecords', ['pid' => $pid, 'fid' => $fid, 'type' => 'XML'])}}" class="btn">Export XML</a>
            </div>
        </div>
    </div>
</div>