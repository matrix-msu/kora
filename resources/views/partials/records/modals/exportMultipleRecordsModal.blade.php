<div class="modal modal-js modal-mask export-mult-records-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Export <span class="count"></span> Records</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body exp-rec">
            <div class="form-group">
                Export <span class="count"></span> form records in the formats of JSON or XML. You may also export <span class="count"></span> record files as a zip.
            </div>
            <div class="form-group mt-m">
                <a href="{{ action('ExportController@exportRecordFiles',['pid' => $form->project_id, 'fid' => $form->id]) }}" class="btn secondary">Export Record Files</a>
            </div>
            <div class="form-group mt-m">
                <a href="{{ action('ExportController@exportSelectedRecords', ['pid' => $form->project_id, 'fid' => $form->id, 'type' => 'JSON', 'rid' => '']) }}" class="btn export-multiple-js">Export JSON</a>
            </div>
            <div class="form-group mt-m">
                <a href="{{ action('ExportController@exportSelectedRecords', ['pid' => $form->project_id, 'fid' => $form->id, 'type' => 'XML', 'rid' => '']) }}" class="btn export-multiple-js">Export XML</a>
            </div>
        </div>
    </div>
</div>