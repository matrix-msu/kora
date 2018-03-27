<div id="jp_container_{{$field->flid}}_{{$record->rid}}" class="jp-video jp-video-270p jp-video-js jp-center" role="application" aria-label="media player"
    video-id="{{$field->flid}}_{{$record->rid}}"
    video-link="{{config('app.storage_url').'files/p'.$form->pid.'/f'.$form->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'}}"
    swf-path="{{config('app.base_path')}}public/assets/javascripts/vendor/jplayer/jquery.jplayer.swf">
    @foreach(explode('[!]',$typedField->video) as $key => $vid)
        @if($vid != '')
            <span class="jp-video-file-js hidden" video-name="{{explode('[Name]',$vid)[1]}}" video-type="{{explode('[Type]',$vid)[1]}}"></span>
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
                <li></li>
            </ul>
        </div>
        <div class="jp-no-solution">
            <span>Update Required</span>
            To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
        </div>
    </div>
</div>