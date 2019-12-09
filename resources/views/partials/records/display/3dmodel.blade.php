@foreach($typedField->processDisplayData($field, $value) as $opt)
    @php
        $name = $opt['name'];
        $parts = explode('.', $name);
        $type = array_pop($parts);
        if(in_array($type, array('stl','obj')))
            $model_link = action('FieldAjaxController@getFileDownload',['kid' => $record->kid, 'filename' => $name]);
    @endphp
@endforeach
<div class="record-data-card model-card">
    <div class="model-wrapper">
        <div class="model-player-div model-player-div-js" model-link="{{$model_link}}" model-id="{{$flid}}_{{$record->id}}"
             model-color="{{$field['options']['ModelColor']}}"
             bg1-color="{{$field['options']['BackColorOne']}}"
             bg2-color="{{$field['options']['BackColorTwo']}}">
            <canvas id="cv{{$flid}}_{{$record->id}}" class="model-player-canvas">
                It seems you are using an outdated browser that does not support canvas :-(
            </canvas>
          </div>
      </div>
      <div class="field-sidebar model-sidebar-js">
        <div class="top">
            <a href="{{ action('FieldController@singleModel', ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id, 'flid' => $flid]) }}" target="_blank" class="field-btn tooltip" tooltip="Open in New Tab">
                <i class="icon icon-external-link"></i>
            </a>
            <a href="{{$model_link}}" class="field-btn tooltip" tooltip="Download Model">
                <i class="icon icon-download"></i>
            </a>
  	    </div>
  	    <div class="bottom">
            <div class="field-btn full-screen-button-js tooltip" tooltip="View Fullscreen">
                <i class="icon icon-maximize"></i>
            </div>
        </div>
      </div>
      <div class="full-screen-modal modal modal-js modal-mask model-modal model-modal-js">
          <div class="content">
              <div class="body">
                  <a href="#" class="modal-toggle modal-toggle-js"><i class="icon icon-cancel"></i></a>
		              <canvas id="cv{{$flid}}_{{$record->id}}-modal-js" class="model-player-canvas">
                      It seems you are using an outdated browser that does not support canvas :-(
                  </canvas>
              </div>
        </div>
    </div>
</div>
