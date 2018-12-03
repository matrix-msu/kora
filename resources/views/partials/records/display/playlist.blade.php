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
                    <i class="icon icon-replay-big audio-button audio-button-js replay-button-js"></i>

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


<!--<div id="jp_container_{{$field->flid}}_{{$record->rid}}" class="jp-video jp-video-270p jp-audio-js jp-center" role="application" aria-label="media player"
    audio-id="{{$field->flid}}_{{$record->rid}}"
    audio-link="{{url('app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$record->rid.'/fl'.$field->flid).'/'}}"
    swf-path="{{public_path('assets/javascripts/vendor/jplayer/jquery.jplayer.swf')}}">
    @foreach(explode('[!]',$typedField->audio) as $key => $aud)
        @if($aud != '')
            <span class="jp-audio-file-js hidden" audio-name="{{explode('[Name]',$aud)[1]}}" audio-type="{{explode('[Type]',$aud)[1]}}"></span>
        @endif
    @endforeach
    <div class="jp-type-playlist">
        <div id="jquery_jplayer_{{$field->flid}}_{{$record->rid}}" class="jp-jplayer"></div>
        <div class="jp-gui">
            <div class="jp-video-play">
                <button class="jp-video-play-icon" role="button" tabindex="0">play</button>
            </div>
            <div class="jp-interface">
                <div class="jp-progress">
                    <div class="jp-seek-bar">
                        <div class="jp-play-bar"></div>
                    </div>
                </div>
                <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
                <div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
                <div class="jp-details">
                    <div class="jp-title" aria-label="title">&nbsp;</div>
                </div>
                <div class="jp-controls-holder">
                    <div class="jp-volume-controls">
                        <button class="jp-mute" role="button" tabindex="0">mute</button>
                        <button class="jp-volume-max" role="button" tabindex="0">max volume</button>
                        <div class="jp-volume-bar">
                            <div class="jp-volume-bar-value"></div>
                        </div>
                    </div>
                    <div class="jp-controls">
                        <button class="jp-previous" role="button" tabindex="0">previous</button>
                        <button class="jp-play" role="button" tabindex="0">play</button>
                        <button class="jp-stop" role="button" tabindex="0">stop</button>
                        <button class="jp-next" role="button" tabindex="0">next</button>
                    </div>
                    <div class="jp-toggles">
                        <button class="jp-full-screen" role="button" tabindex="0">full screen</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="jp-playlist">
            <ul>
                <!-- The method Playlist.displayPlaylist() uses this unordered list -->
                <!--<li></li>
            </ul>
        </div>
        <div class="jp-no-solution">
            <span>Update Required</span>
            To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
        </div>
    </div>
</div>-->
