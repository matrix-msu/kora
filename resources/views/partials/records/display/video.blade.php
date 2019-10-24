@foreach($typedField->processDisplayData($field, $value) as $vid)
    @php
        $name = $vid['name'];
        $caption = $vid['caption'];
        $link = action('FieldAjaxController@publicRecordFile',['kid' => $record->kid, 'filename' => $name]);
    @endphp
    <div class="record-data-card">
      <div class="field-display video-field-display video-field-display-js">
        <video height="300" width="auto" controls>
          <source data-filename="{{$name}}" src="{{$link}}" type="{{$vid['type']}}">

          Your browser does not support the video tag.
        </video>
      </div>

        @if($caption!='')
            <div class="video-info">{{$caption}}</div>
        @endif

      <div class="field-sidebar video-sidebar video-sidebar-js">
          <div class="top">
              <a href="{{$link}}" class="field-btn tooltip" tooltip="Open in New Tab" target="_blank">
                  <i class="icon icon-external-link"></i>
              </a>

              <a href="{{ action('FieldAjaxController@getFileDownload', ['kid' => $record->kid, 'filename' => $name]) }}" class="field-btn tooltip" tooltip="Download Video">
                  <i class="icon icon-download"></i>
              </a>
          </div>

          <div class="bottom">
              <div class="field-btn full-screen-button-js tooltip" tooltip="View Fullscreen">
                  <i class="icon icon-maximize"></i>
              </div>
          </div>
      </div>
    </div>
@endforeach
