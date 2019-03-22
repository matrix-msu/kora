@foreach($typedField->processDisplayData($field, $value) as $opt)
    <div class="record-data-card">
        <div class="field-display document-field-display">
            @if($opt != '')
                @php
                    $ogName = $opt['original_name'];
                    $locName = $opt['local_name'];
                    $size = $opt['size'];
                    $link = action('FieldAjaxController@getFileDownload',['kid' => $record->kid, 'filename' => $locName]);
                @endphp
                <div>
                    <p class="filename"><a class="documents-link underline-middle-hover" href="{{$link}}">{{$ogName}}</a></p>
                    <p class="file-size">File size: {{$typedField->formatBytes($size)}}</p>
                </div>
            @endif
        </div>

        <div class="field-sidebar document-sidebar document-sidebar-js">
            <div class="top">
                <a class="field-btn external-button-js" target="_blank" href="{{action('FieldAjaxController@singleResource',['kid' => $record->kid, 'filename' => $locName])}}">
                    <i class="icon icon-external-link"></i>
                </a>

                <a href="{{action('FieldAjaxController@getFileDownload', ['kid' => $record->kid, 'filename' => $locName])}}"
                   class="field-btn">
                    <i class="icon icon-download"></i>
                </a>
            </div>
        </div>
    </div>
@endforeach
