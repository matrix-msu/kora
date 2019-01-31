@foreach($typedField->processDisplayData($field, $value) as $aud)
    @php
        $filename = $aud['name'];
    @endphp
    <div class="record-data-card">
        <div class="field-display audio-field-display">
            <p class="audio-filename">{{$filename}}</p>

            <div class="audio-container">
                <i class="icon icon-play audio-button audio-button-js play-button-js active"></i>
                <i class="icon icon-pause-big audio-button audio-button-js pause-button-js"></i>
                <i class="icon icon-replay-circle audio-button audio-button-js replay-button-js"></i>

                <div class="slider slider-js no-select ml-m">
                    <div class="slider-button slider-button-js"><div class="inner-button"></div></div>

                    <span class="current-time current-time-js mr-sm">0:00</span>
                    <div class="slider-bar slider-bar-js"></div>
                    <div class="slider-progress-bar slider-progress-bar-js"></div>
                    <span class="duration-time duration-time-js ml-sm">0:00</span>
                </div>

                <audio class="audio-clip audio-clip-js">
                    <source src="{{url('app/files/'.$aud['url'].'/'.$filename)}}" type="{{$aud['type']}}">
                    Your browser does not support the audio element
                </audio>
            </div>
        </div>

        <div class="field-sidebar audio-sidebar audio-sidebar-js">
            <div class="top">
                <a href="{{url('projects/'.$form->project_id.'/forms/'.$form->id.'/records/'.$record->id.'/resource/'.$filename)}}" class="field-btn" target="_blank">
                    <i class="icon icon-external-link"></i>
                </a>

                <a href="{{ action('FieldAjaxController@getFileDownload', ['kid' => $record->kid, 'filename' => $filename]) }}" class="field-btn">
                    <i class="icon icon-download"></i>
                </a>
            </div>
        </div>
    </div>
@endforeach
