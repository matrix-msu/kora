<div class="preset card all {{ $index == 0 ? 'active' : '' }}" id="{{$preset->id}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            @if(\App\Http\Controllers\RecordController::exists($preset->record_kid))
                <a class="title" href="{{ action("RecordController@show",
                    ["pid" => $form->pid, "fid" => $form->fid, "rid" => $preset->record_kid]) }}">
                    <span class="name underline-middle-hover">{{$preset->record_kid}}</span>
                    <span class="sub-title">
                        {{$preset->name}}
                    </span>
                </a>
            @else
                <a class="title">
                    <span class="name record-gone">{{$preset->record_kid}}</span>
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
            @php
                $format = $preset->preset;
                $presetData = $format['data'];
            @endphp
            @foreach($presetData as $flid => $data)
                @php
                    $field = $form->layout['fields'][$flid];
                    $fieldMod = $form->getFieldModel($field['type']);

                    if(is_null($data))
                        $data = 'No Field Data';
                    else
                        $data = $fieldMod->processRevisionData($data);
                @endphp
                <div class="preset-value-div">
                    <div class="preset-value-title">{{$field['name']}}</div>
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

            @if(\App\Http\Controllers\RecordController::exists($preset->record_kid))
                <a class="quick-action underline-middle-hover" href="{{ action("RecordController@show",
                    ["pid" => $form->pid, "fid" => $form->fid, "rid" => $preset->record_kid]) }}">
                    <span>View Original Record</span>
                    <i class="icon icon-arrow-right"></i>
                </a>
            @endif
        </div>
    </div>
</div>