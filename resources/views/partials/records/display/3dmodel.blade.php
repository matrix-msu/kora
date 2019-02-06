@foreach(explode('[!]',$typedField->model) as $opt)
    @if($opt != '')
        <?php
        $name = explode('[Name]',$opt)[1];
        $parts = explode('.', $name);
        $type = array_pop($parts);
        if(in_array($type, array('stl','obj')))
		$model_link = action('FieldAjaxController@getFileDownload',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name]);
        ?>
    @endif
@endforeach
<div class="record-data-card">
    <div class="model-wrapper">
        <div class="model-player-div model-player-div-js" model-link="{{$model_link}}" model-id="{{$field->flid}}_{{$record->rid}}"
             model-color="{{\App\Http\Controllers\FieldController::getFieldOption($field,'ModelColor')}}"
             bg1-color="{{\App\Http\Controllers\FieldController::getFieldOption($field,'BackColorOne')}}"
             bg2-color="{{\App\Http\Controllers\FieldController::getFieldOption($field,'BackColorTwo')}}">
            <canvas id="cv{{$field->flid}}_{{$record->rid}}" class="model-player-canvas">
                It seems you are using an outdated browser that does not support canvas :-(
            </canvas>
        </div>
    </div>
    <div class="field-sidebar model-sidebar model-sidebar-js">
        <div class="top">
            <div class="field-btn external-button-js"><i class="icon icon-external-link"></i></div>
            <a href="{{$model_link}}" class="field-btn"><i class="icon icon-download"></i></a>
	</div>
	<div class="bottom">
            <div class="field-btn full-screen-button-js"><i class="icon icon-maximize"></i></div>
        </div>
    </div>
    <div class="full-screen-modal modal modal-js modal-mask model-modal model-modal-js">
        <div class="content">
            <div class="body">
                <a href="#" class="modal-toggle modal-toggle-js"><i class="icon icon-cancel"></i></a>
		<canvas id="cv{{$field->flid}}_{{$record->rid}}-modal-js" class="model-player-canvas">
                    It seems you are using an outdated browser that does not support canvas :-(
                </canvas>
            </div> 
        </div>
    </div>
</div>
