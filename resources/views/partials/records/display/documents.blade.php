@foreach($typedField->processDisplayData($field, $value) as $opt)
    <div class="record-data-card">
        <div class="field-display document-field-display">
            @if($opt != '')
                @php
                    $name = $opt['name'];
                    $size = $opt['size'];
                    $link = action('FieldAjaxController@getFileDownload',['kid' => $record->kid, 'filename' => $name]);
                    $pubLink = action('FieldAjaxController@publicRecordFile',['kid' => $record->kid, 'filename' => $name]);
                @endphp
                <div>
                    <p class="filename"><a class="documents-link underline-middle-hover" href="{{$link}}">{{$name}}</a></p>
                    <p class="file-size">File size: {{formatBytes($size)}}</p>
                </div>
            @endif
        </div>

        <div class="field-sidebar document-sidebar document-sidebar-js">
            <div class="top">
                <a class="field-btn external-button-js tooltip" tooltip="Open in New Tab" target="_blank" href="{{$pubLink}}">
                    <i class="icon icon-external-link"></i>
                </a>

                <a href="{{action('FieldAjaxController@getFileDownload', ['kid' => $record->kid, 'filename' => $name])}}"
                   class="field-btn tooltip" tooltip="Download Document">
                    <i class="icon icon-download"></i>
                </a>
            </div>
        </div>
    </div>
@endforeach
