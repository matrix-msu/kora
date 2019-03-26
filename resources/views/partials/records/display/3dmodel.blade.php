@foreach($typedField->processDisplayData($field, $value) as $opt)
    @php
        $ogName = $opt['original_name'];
        $locName = $opt['local_name'];
        $parts = explode('.', $locName);
        $type = array_pop($parts);
        if(in_array($type, array('stl','obj')))
            $model_link = action('FieldAjaxController@getFileDownload',['kid' => $record->kid, 'filename' => $locName]);
    @endphp
@endforeach
<div class="record-data-card">
    <div class="model-wrapper">
        <div class="model-player-div model-player-div-js" model-link="{{$model_link}}" model-id="{{$flid}}_{{$record->id}}"
             model-color="{{$field['options']['ModelColor']}}"
             bg1-color="{{$field['options']['BackColorOne']}}"
             bg2-color="{{$field['options']['BackColorTwo']}}">
            <canvas id="cv{{$flid}}_{{$record->id}}" class="model-player-canvas">
                It seems you are using an outdated browser that does not support canvas :-(
            </canvas><br>
            {{--    <button id="cvfs{{$field->flid}}_{{$record->rid}}" type="button">FULLSCREEN</button>--}}
        </div>
    </div>
</div>
