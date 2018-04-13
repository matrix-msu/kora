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
                Export all form records in the formats of
                <a class="export-record-link" href="{{action('ExportController@exportRecords', ['pid' => $pid, 'fid' => $fid, 'type' => 'JSON'])}}">JSON</a>
                or <a class="export-record-link" href="{{action('ExportController@exportRecords', ['pid' => $pid, 'fid' => $fid, 'type' => 'XML'])}}">XML</a>.
                You may also export all
                <a class="export-record-link" href="{{ action('ExportController@exportRecordFiles',['pid' => $form->pid, 'fid' => $form->fid]) }}">record files here</a>.
            </div>
        </div>
    </div>
</div>