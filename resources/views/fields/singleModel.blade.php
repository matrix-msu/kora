@extends('app-plain', ['page_title' => 'Model', 'page_class' => 'field-single-model'])

@section('body')
    @php
      $fileData = $typedField->processDisplayData($field, $value)[0];
      $filename = $fileData['name'];
      $filepath = $fileData['url'];
      $filesize = $fileData['size'];
      $filetype = $fileData['type'];

      $maxFiles = $field['options']['MaxFiles'];
      $fieldSize = $field['options']['FieldSize'];
      $fileTypes = $field['options']['FileTypes'];
      $modelColor = $field['options']['ModelColor'];
      $backColorOne = $field['options']['BackColorOne'];
      $backColorTwo = $field['options']['BackColorTwo'];

      $model_link = action('FieldAjaxController@getFileDownload',['kid' => $record->kid, 'filename' => $filename]);
    @endphp

    <div class="record-data-card">
        <div class="model-wrapper">
            <div class="model-player-div model-player-div-js" model-link="{{$model_link}}" model-id="{{$flid}}_{{$rid}}"
                model-color="{{$modelColor}}"
                bg1-color="{{$backColorOne}}"
                bg2-color="{{$backColorTwo}}">
                <canvas id="cv{{$flid}}_{{$rid}}" class="model-player-canvas">
                    It seems you are using an outdated browser that does not support canvas :-(
                </canvas>
            </div>
        </div>
    </div>
@stop


@section('javascripts')
    @include('partials.records.javascripts')

    <script type="text/javascript">
        Kora.Records.Show();
    </script>
@stop
