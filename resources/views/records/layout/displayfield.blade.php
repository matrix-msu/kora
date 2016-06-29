<div>
    <span><b>{{ $field->name }}:</b> </span>
    <span>
        @if($field->type=='Text')
            @foreach($record->textfields as $tf)
                @if($tf->flid == $field->flid)
                    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==1)
                        <br>
                        <?php echo nl2br($tf->text) ?>
                    @else
                        {{ $tf->text }}
                    @endif
                @endif
            @endforeach
        @elseif($field->type=='Rich Text')
            @foreach($record->richtextfields as $rtf)
                @if($rtf->flid == $field->flid)
                    <?php echo $rtf->rawtext ?>
                @endif
            @endforeach
        @elseif($field->type=='Number')
            @foreach($record->numberfields as $nf)
                @if($nf->flid == $field->flid)
                    <?php
                        echo $nf->number + 0;
                        if($nf->number!='')
                            echo ' '.\App\Http\Controllers\FieldController::getFieldOption($field,'Unit');
                    ?>
                @endif
            @endforeach
        @elseif($field->type=='List')
            @foreach($record->listfields as $lf)
                @if($lf->flid == $field->flid)
                    {{ $lf->option }}
                @endif
            @endforeach
        @elseif($field->type=='Multi-Select List')
            @foreach($record->multiselectlistfields as $mslf)
                @if($mslf->flid == $field->flid)
                    @foreach(explode('[!]',$mslf->options) as $opt)
                        <div>{{ $opt }}</div>
                    @endforeach
                @endif
            @endforeach
        @elseif($field->type=='Generated List')
            @foreach($record->generatedlistfields as $glf)
                @if($glf->flid == $field->flid)
                    @foreach(explode('[!]',$glf->options) as $opt)
                        <div>{{ $opt }}</div>
                    @endforeach
                @endif
            @endforeach
        @elseif($field->type=='Combo List')
            @foreach($record->combolistfields as $clf)
                @if($clf->flid == $field->flid)
                    <?php
                        $cmbName1 = \App\ComboListField::getComboFieldName($field,'one');
                        $cmbName2 = \App\ComboListField::getComboFieldName($field,'two');

                        $oneType = \App\ComboListField::getComboFieldType($field,'one');
                        $twoType = \App\ComboListField::getComboFieldType($field,'two');

                        $valArray = explode('[!val!]',$clf->options);
                    ?>
                    <div style="overflow: auto">
                        <div>
                            <span style="float:left;width:50%;margin-bottom:10px"><b>{{$cmbName1}}</b></span>
                            <span style="float:left;width:50%;margin-bottom:10px"><b>{{$cmbName2}}</b></span>
                        </div>
                        @for($i=0;$i<sizeof($valArray);$i++)
                            <div>
                                @if($oneType=='Text' | $oneType=='List')
                                    <?php $value1 = explode('[!f1!]',$valArray[$i])[1]; ?>
                                    <span style="float:left;width:50%;margin-bottom:10px">{{$value1}}</span>
                                @elseif($oneType=='Number')
                                    <?php
                                    $value1 = explode('[!f1!]',$valArray[$i])[1];
                                    $unit = \App\ComboListField::getComboFieldOption($field,'Unit','one');
                                    if($unit!=null && $unit!=''){
                                        $value1 .= ' '.$unit;
                                    }
                                    ?>
                                    <span style="float:left;width:50%;margin-bottom:10px">{{$value1}}</span>
                                @elseif($oneType=='Multi-Select List' | $oneType=='Generated List')
                                    <?php
                                    $value1 = explode('[!f1!]',$valArray[$i])[1];
                                    $value1Array = explode('[!]',$value1);
                                    ?>

                                    <span style="float:left;width:50%;margin-bottom:10px">
                                        @foreach($value1Array as $val)
                                            <div>{{$val}}</div>
                                        @endforeach
                                    </span>
                                @endif

                                @if($twoType=='Text' | $twoType=='List')
                                    <?php $value2 = explode('[!f2!]',$valArray[$i])[1]; ?>
                                    <span style="float:left;width:50%;margin-bottom:10px">{{$value2}}</span>
                                @elseif($twoType=='Number')
                                    <?php
                                    $value2 = explode('[!f2!]',$valArray[$i])[1];
                                    $unit = \App\ComboListField::getComboFieldOption($field,'Unit','two');
                                    if($unit!=null && $unit!=''){
                                        $value2 .= ' '.$unit;
                                    }
                                    ?>
                                    <span style="float:left;width:50%;margin-bottom:10px">{{$value2}}</span>
                                @elseif($twoType=='Multi-Select List' | $twoType=='Generated List')
                                    <?php
                                    $value2 = explode('[!f2!]',$valArray[$i])[1];
                                    $value2Array = explode('[!]',$value2);
                                    ?>

                                    <span style="float:left;width:50%;margin-bottom:10px">
                                        @foreach($value2Array as $val)
                                            <div>{{$val}}</div>
                                        @endforeach
                                    </span>
                                @endif
                            </div>
                        @endfor
                    </div>
                @endif
            @endforeach
        @elseif($field->type=='Date')
            @foreach($record->datefields as $df)
                @if($df->flid == $field->flid)
                    @if($df->circa==1 && \App\Http\Controllers\FieldController::getFieldOption($field,'Circa')=='Yes')
                        {{'circa '}}
                    @endif
                    @if($df->month==0 && $df->day==0)
                        {{$df->year}}
                    @elseif($df->day==0)
                        {{ $df->month.' '.$df->year }}
                    @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='MMDDYYYY')
                        {{$df->month.'-'.$df->day.'-'.$df->year}}
                    @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='DDMMYYYY')
                        {{$df->day.'-'.$df->month.'-'.$df->year}}
                    @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='YYYYMMDD')
                        {{$df->year.'-'.$df->month.'-'.$df->day}}
                    @endif
                    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Era')=='Yes')
                        {{' '.$df->era}}
                    @endif
                @endif
            @endforeach
        @elseif($field->type=='Schedule')
            @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Calendar')=='No')
                @foreach($record->schedulefields as $sf)
                    @if($sf->flid == $field->flid)
                        @foreach(explode('[!]',$sf->events) as $event)
                            <div>{{ $event }}</div>
                        @endforeach
                    @endif
                @endforeach
            @else
                @foreach($record->schedulefields as $sf)
                    @if($sf->flid == $field->flid)
                        <div id='calendar{{$field->flid}}'></div>
                        <script>
                            $('#calendar{{$field->flid}}').fullCalendar({
                                header: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'month,agendaWeek,agendaDay'
                                },
                                events: [
                                    @foreach(explode('[!]',$sf->events) as $event)
                                        {
                                        <?php
                                            $nameTime = explode(': ',$event);
                                            $times = explode(' - ',$nameTime[1]);
                                            $allDay = true;
                                            if(strpos($nameTime[1],'PM') | strpos($nameTime[1],'AM')){
                                                $allDay = false;
                                            }
                                        ?>
                                        title: '{{ $nameTime[0] }}',
                                        start: '{{ $times[0] }}',
                                        end: '{{ $times[1] }}',
                                        @if($allDay)
                                            allDay: true
                                        @else
                                            allDay: false
                                        @endif
                                    },
                                    @endforeach
                                ]
                            });
                        </script>
                    @endif
                @endforeach
            @endif
        @elseif($field->type=='Geolocator')
            @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Map')=='No')
                @foreach($record->geolocatorfields as $gf)
                    @if($gf->flid == $field->flid)
                        @foreach(explode('[!]',$gf->locations) as $opt)
                            @if(\App\Http\Controllers\FieldController::getFieldOption($field,'DataView')=='LatLon')
                                <div>{{ explode('[Desc]',$opt)[1].': '.explode('[LatLon]',$opt)[1] }}</div>
                            @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'DataView')=='UTM')
                                <div>{{ explode('[Desc]',$opt)[1].': '.explode('[UTM]',$opt)[1] }}</div>
                            @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'DataView')=='Textual')
                                <div>{{ explode('[Desc]',$opt)[1].': '.explode('[Address]',$opt)[1] }}</div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @else
                @foreach($record->geolocatorfields as $gf)
                    @if($gf->flid == $field->flid)
                        <div id="map{{$field->flid}}" style="height:270px;"></div>
                        <?php $locs = array(); ?>
                        @foreach(explode('[!]',$gf->locations) as $location)
                            <?php
                                $loc = array();
                                $desc = explode('[Desc]',$location)[1];
                                $x = explode(',', explode('[LatLon]',$location)[1])[0];
                                $y = explode(',', explode('[LatLon]',$location)[1])[1];

                                $loc['desc'] = $desc;
                                $loc['x'] = $x;
                                $loc['y'] = $y;

                                array_push($locs,$loc);
                            ?>
                        @endforeach
                    <script>
                        var map{{$field->flid}} = L.map('map{{$field->flid}}').setView([{{$locs[0]['x']}}, {{$locs[0]['y']}}], 13);
                        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(map{{$field->flid}});
                        @foreach($locs as $loc)
                            var marker = L.marker([{{$loc['x']}}, {{$loc['y']}}]).addTo(map{{$field->flid}});
                            marker.bindPopup("{{$loc['desc']}}");
                        @endforeach
                    </script>
                    @endif
                @endforeach
            @endif
        @elseif($field->type=='Documents')
            @foreach($record->documentsfields as $df)
                @if($df->flid == $field->flid)
                    @foreach(explode('[!]',$df->documents) as $opt)
                        @if($opt != '')
                            <?php
                            $name = explode('[Name]',$opt)[1];
                            $link = action('FieldAjaxController@getFileDownload',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name]);
                            ?>
                            <div><a href="{{$link}}">{{$name}}</a></div>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @elseif($field->type=='Gallery')
            @foreach($record->galleryfields as $gf)
                @if($gf->flid == $field->flid)
                    <div class="gal{{$field->flid}}">
                        @foreach(explode('[!]',$gf->images) as $img)
                            @if($img != '')
                                <?php
                                $name = explode('[Name]',$img)[1];
                                $link = action('FieldAjaxController@getImgDisplay',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name, 'type' => 'normal']);
                                ?>
                                <div><img class="img-responsive" src="{{$link}}" alt="{{$name}}"></div>
                            @endif
                        @endforeach
                    </div>
                    <script>
                        $('.gal{{$field->flid}}').slick({
                            dots: true,
                            infinite: true,
                            speed: 500,
                            fade: true,
                            cssEase: 'linear'
                        });
                    </script>
                @endif
            @endforeach
        @elseif($field->type=='Playlist')
            @foreach($record->playlistfields as $pf)
                @if($pf->flid == $field->flid)
                    <div id="jp_container_{{$field->flid}}" class="jp-video jp-video-270p" role="application" aria-label="media player">
                        <div class="jp-type-playlist">
                            <div id="jquery_jplayer_{{$field->flid}}" class="jp-jplayer"></div>
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
                    <script>
                        var cssSelector = { jPlayer: "#jquery_jplayer_{{$field->flid}}", cssSelectorAncestor: "#jp_container_{{$field->flid}}" };
                        var playlist = [
                            @foreach(explode('[!]',$pf->audio) as $key => $aud)
                                @if($aud != '')
                                    <?php
                                    $name = explode('[Name]',$aud)[1];
                                    $link = env('BASE_URL').'storage/app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$name;
                                    ?>
                                    {
                                        title: "{{$name}}",
                                        @if(explode('[Type]',$aud)[1]=="audio/mpeg")
                                            mp3: "{{$link}}"
                                        @elseif(explode('[Type]',$aud)[1]=="audio/ogg")
                                            oga: "{{$link}}"
                                        @elseif(explode('[Type]',$aud)[1]=="audio/x-wav")
                                            wav: "{{$link}}"
                                        @endif
                                    },
                                @endif
                            @endforeach
                        ];
                        var options = {
                            swfPath: "{{env('BASE_PATH')}}public/jplayer/jquery.jplayer.swf",
                            supplied: "mp3, oga, wav"
                        };
                        var myPlaylist = new jPlayerPlaylist(cssSelector, playlist, options);
                    </script>
                @endif
            @endforeach
        @elseif($field->type=='Video')
            @foreach($record->videofields as $vf)
                @if($vf->flid == $field->flid)
                    <div id="jp_container_{{$field->flid}}" class="jp-video jp-video-270p" role="application" aria-label="media player">
                        <div class="jp-type-playlist">
                            <div id="jquery_jplayer_{{$field->flid}}" class="jp-jplayer"></div>
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
                    <script>
                        var cssSelector = { jPlayer: "#jquery_jplayer_{{$field->flid}}", cssSelectorAncestor: "#jp_container_{{$field->flid}}" };
                        var playlist = [
                            @foreach(explode('[!]',$vf->video) as $key => $vid)
                                @if($vid != '')
                                    <?php
                                    $name = explode('[Name]',$vid)[1];
                                    $link = env('BASE_URL').'storage/app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$name;
                                    ?>
                                    {
                                        title: "{{$name}}",
                                        @if(explode('[Type]',$vid)[1]=="video/mp4")
                                            m4v: "{{$link}}"
                                        @elseif(explode('[Type]',$vid)[1]=="video/ogg")
                                            ogv: "{{$link}}"
                                        @endif
                                    },
                                @endif
                            @endforeach
                        ];
                        var options = {
                            swfPath: "{{env('BASE_PATH')}}public/jplayer/jquery.jplayer.swf",
                            supplied: "m4v, ogv"
                        };
                        var myPlaylist = new jPlayerPlaylist(cssSelector, playlist, options);
                    </script>
                @endif
            @endforeach
        @elseif($field->type=='3D-Model')
            @foreach($record->modelfields as $mf)
                @if($mf->flid == $field->flid)
                    @foreach(explode('[!]',$mf->model) as $opt)
                        @if($opt != '')
                            <?php
                            $name = explode('[Name]',$opt)[1];
                            $link = action('FieldAjaxController@getFileDownload',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name]);
                            ?>
                            <div style="width:800px; margin:auto; position:relative;">
                                <canvas id="cv{{$field->flid}}" style="border: 1px solid;" width="750" height="400">
                                    It seems you are using an outdated browser that does not support canvas :-(
                                </canvas>
                            </div>

                            <script type="text/javascript">
                                var viewer = new JSC3D.Viewer(document.getElementById('cv{{$field->flid}}'));
                                viewer.setParameter('SceneUrl',         '{{$link}}');
                                viewer.setParameter('ModelColor',       '#CAA618');
                                viewer.setParameter('BackgroundColor1', '#E5D7BA');
                                viewer.setParameter('BackgroundColor2', '#383840');
                                viewer.setParameter('RenderMode',       'flat');
                                viewer.init();
                                viewer.update();
                            </script>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @elseif($field->type=='Associator')
            @foreach($record->associatorfields as $af)
                @if($af->flid == $field->flid)
                    @foreach(explode('[!]',$af->records) as $opt)
                        <div>{{ $opt }}</div>
                    @endforeach
                @endif
            @endforeach
        @endif
    </span>
</div>