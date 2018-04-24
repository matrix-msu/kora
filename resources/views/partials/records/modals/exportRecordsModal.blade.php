<div class="modal modal-js modal-mask export-records-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Export All Records</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="form-group">
                Export all form records in the formats of JSON or XML. You may also export all record files as a zip.
            </div>
            <div class="form-group half mt-m">
                <a href="{{action('ExportController@exportRecords', ['pid' => $pid, 'fid' => $fid, 'type' => 'JSON'])}}" class="btn secondary pr-xs">Export JSON</a>
            </div>
            <div class="form-group half mt-m">
                <a href="{{action('ExportController@exportRecords', ['pid' => $pid, 'fid' => $fid, 'type' => 'XML'])}}" class="btn secondary pl-xs">Export XML</a>
            </div>
            <div class="form-group">
                <a href="{{ action('ExportController@exportRecordFiles',['pid' => $form->pid, 'fid' => $form->fid]) }}" class="btn">Export Record Files</a>
            </div>
        </div>
    </div>
</div>