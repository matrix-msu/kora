@foreach($typedField->processDisplayData($field, $value) as $vid)
    @php
        $name = $vid['name'];
        $link = action('FieldAjaxController@publicRecordFile',['kid' => $record->kid, 'filename' => $name]);
    @endphp
    <div class="record-data-card">
      <div class="field-display video-field-display video-field-display-js">
        <video height="300" width="auto" controls>
          <source data-filename="{{$name}}" src="{{$link}}" type="{{$vid['type']}}">

          Your browser does not support the video tag.
        </video>
      </div>

      <div class="field-sidebar video-sidebar video-sidebar-js">
          <div class="top">
              <a href="{{$link}}" class="field-btn" target="_blank">
                  <i class="icon icon-external-link"></i>
              </a>

              <a href="{{ action('FieldAjaxController@getFileDownload', ['kid' => $record->kid, 'filename' => $name]) }}" class="field-btn">
                  <i class="icon icon-download"></i>
              </a>
          </div>

          <div class="bottom">
              <div class="field-btn full-screen-button-js">
                  <i class="icon icon-maximize"></i>
              </div>
          </div>
      </div>
    </div>
@endforeach
