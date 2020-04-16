<div class="modal modal-js modal-mask export-mult-records-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title export-mult-records-title-js">Export <span class="count"></span> Records</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body exp-rec">
            <div class="form-group export-mult-files-desc-js">
                Export <span class="count"></span> form records in the formats of JSON or XML. You may also export <span class="count"></span> record files as a zip.
            </div>
            <div class="form-group mt-m">
                <a href="#" class="btn export-mult-begin-files-js" token="{{ csrf_token() }}"
                   startURL="{{ action('ExportController@prepRecordFiles',['pid' => $form->project_id, 'fid' => $form->id]) }}"
                   checkURL="{{ action('ExportController@checkRecordFiles',['pid' => $form->project_id, 'fid' => $form->id]) }}"
                   endURL="{{ action('ExportController@exportRecordFiles',['pid' => $form->project_id, 'fid' => $form->id, 'name' => '']) }}"
                >Export Record Files</a>
            </div>
            <div class="form-group mt-m">
                <form class="export-multiple-js" method="post" action="{{ action('ExportController@exportSelectedRecords', ['pid' => $form->project_id, 'fid' => $form->id, 'type' => 'JSON']) }}">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div>
                        {!! Form::submit('Export JSON',['class' => 'btn']) !!}
                    </div>
                </form>
            </div>
            <div class="form-group mt-m">
                <form class="export-multiple-js" method="post" action="{{ action('ExportController@exportSelectedRecords', ['pid' => $form->project_id, 'fid' => $form->id, 'type' => 'XML']) }}">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div>
                        {!! Form::submit('Export XML',['class' => 'btn']) !!}
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>