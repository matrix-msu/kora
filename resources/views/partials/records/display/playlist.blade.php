@foreach(explode('[!]',$typedField->audio) as $key => $aud)
    @if($aud != '')
        <?php
            $filename = explode('[Name]',$aud)[1];
        ?>
        <div class="record-data-card">
            <div class="field-display audio-field-display">
                <p class="audio-filename">{{explode('[Name]',$aud)[1]}}</p>

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
                        <source src="{{url('app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$filename)}}" type="{{explode('[Type]',$aud)[1]}}">
                        Your browser does not support the audio element
                    </audio>
                </div>
            </div>

            <div class="field-sidebar audio-sidebar audio-sidebar-js">
                <div class="top">
                    <a href="{{url('projects/'.$form->pid.'/forms/'.$form->fid.'/records/'.$record->rid.'/fields/'.$field->flid.'/'.$filename)}}" class="field-btn" target="_blank">
                        <i class="icon icon-external-link"></i>
                    </a>

                    <a href="{{ action('FieldAjaxController@getFileDownload', ['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $filename]) }}" class="field-btn">
                        <i class="icon icon-download"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif
@endforeach
