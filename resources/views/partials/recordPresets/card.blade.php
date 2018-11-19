<div class="preset card all {{ $index == 0 ? 'active' : '' }}" id="{{$preset->id}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            @if(\App\Http\Controllers\RecordController::exists($preset->rid))
                <a class="title" href="{{ action("RecordController@show",
                    ["pid" => $form->pid, "fid" => $form->fid, "rid" => $preset->rid]) }}">
                    <span class="name underline-middle-hover">{{$form->pid}}-{{$form->fid}}-{{$preset->rid}}</span>
                    <span class="sub-title">
                        {{$preset->name}}
                    </span>
                </a>
            @else
                <a class="title">
                    <span class="name record-gone">{{$form->pid}}-{{$form->fid}}-{{$preset->rid}}</span>
                    <span class="sub-title change-name-{{$preset->id}}-js">
                        {{$preset->name}}
                    </span>
                </a>
            @endif
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle preset-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content {{ $index == 0 ? 'active' : '' }}">
        <div class="description">
            <?php
                $format = json_decode($preset->preset,true);

                $presetData = $format['data'];
            ?>
            @foreach($presetData as $pd)
                <?php
                    $field = \App\Http\Controllers\FieldController::getField($pd['flid']);
                    $key = array_keys($pd)[2];
                    $data = $pd[$key];

                    if(is_null($data)) {
                        $data = 'No Field Data';
                    } else {
                        //TODO::modular?
                        switch($field->type) {
                            case 'Date':
                                $stringDate = '';
                                if($data['circa']) {$stringDate .= 'circa ';}
                                $stringDate .= implode('/', array($data['month'],$data['day'],$data['year']));
                                $stringDate .= ' '.$data['era'];
                                $data = $stringDate;
                                break;
                            case 'Documents':
                            case 'Model':
                            case 'Playlist':
                            case 'Video':
                                $stringFile = '';
                                foreach($data as $file) {
                                    $stringFile .= '<div>'.explode('[Name]',$file)[1].'</div>';
                                }
                                $data = $stringFile;
                                break;
                            case 'Gallery':
                                $names = $data;
                                $captions = $pd['captions'];
                                $stringFile = '';
                                for($gi=0;$gi<count($names);$gi++) {
                                    $capString = '';
                                    if($captions[$gi] != '')
                                        $capString = ' - '.$captions[$gi];
                                    $stringFile .= '<div>'.explode('[Name]',$names[$gi])[1].$capString.'</div>';
                                }
                                $data = $stringFile;
                                break;
                                break;
                            case 'Multi-Select List':
                            case 'Generated List':
                            case 'Schedule':
                            case 'Associator':
                                $stringList = '';
                                foreach($data as $listItem) {
                                    $stringList .= '<div>'.$listItem.'</div>';
                                }
                                $data = $stringList;
                            break;
                            case 'Geolocator':
                                $stringLoc = '';
                                foreach($data as $loc) {
                                    $stringLoc .= '<div>'.explode('[Desc]',$loc)[1].': '.explode('[LatLon]',$loc)[1].'</div>';
                                }
                                $data = $stringLoc;
                                break;
                            case 'Combo List':
                                $stringCombo = '';
                                foreach($data as $comboItem) {
                                    $stringCombo .= '<div>'.explode('[!f1!]',$comboItem)[1].' ~~~ '.explode('[!f2!]',$comboItem)[1].'</div>';
                                }
                                $data = $stringCombo;
                                break;
                            default:
                                break;
                        }
                    }
                ?>
                <div class="preset-value-div">
                    <div class="preset-value-title">{{$field->name}}</div>
                    <div>{!! $data !!}</div>
                </div>
            @endforeach
        </div>

        <div class="footer">
            <a class="quick-action trash-container left danger delete-preset-js tooltip" presetid="{{$preset->id}}" href="#" tooltip="Delete Preset">
                <i class="icon icon-trash"></i>
            </a>

            <a class="quick-action underline-middle-hover change-preset-js" presetid="{{$preset->id}}" href="#">
                <i class="icon icon-edit-little"></i>
                <span>Change Preset Name</span>
            </a>

            @if(\App\Http\Controllers\RecordController::exists($preset->rid))
                <a class="quick-action underline-middle-hover" href="{{ action("RecordController@show",
                    ["pid" => $form->pid, "fid" => $form->fid, "rid" => $preset->rid]) }}">
                    <span>View Original Record</span>
                    <i class="icon icon-arrow-right"></i>
                </a>
            @endif
        </div>
    </div>
</div>