<div class="form-group clist-input-form-group combo-value-div-js-{{$flid}} mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::hidden($flid, true, ['id' => $flid]) !!}

    @php
        $oneType = $field['one']['type'];
        $twoType = $field['two']['type'];
        $oneName = $field['one']['name'];
        $twoName = $field['two']['name'];

        $recInputone = $form->getFieldModel($oneType)::FIELD_INPUT_VIEW;
        $recInputtwo = $form->getFieldModel($twoType)::FIELD_INPUT_VIEW;

        $items = $editRecord ? $typedField->retrieve($flid, $form->id, $record->{$flid}) : $field['one']['default'];
    @endphp

    <div class="combo-list-input combo-list-display-js preset-clear-combo-js">
        <div class="mb-sm">
            <span class="combo-column combo-title">{{$oneName}}</span>
            <span class="combo-column combo-title">{{$twoName}}</span>
        </div>
        <div class="combo-value-item-container-js">
            @if(!is_null($items))
                @for($i=0;$i<count($items);$i++)
                    <div class="combo-value-item combo-value-item-js">

                        <span class="move-actions">
                            <a class="action move-action-js up-js" href="">
                                <i class="icon icon-arrow-up"></i>
                            </a>

                            <a class="action move-action-js down-js" href="">
                                <i class="icon icon-arrow-down"></i>
                            </a>
                        </span>
                        <span class="combo-delete delete-combo-value-js tooltip" tooltip="Delete Combo Value"><i class="icon icon-trash"></i></span>
                        @foreach(['one', 'two'] as $seq)
                            @php
                                $value = $display = null;
                                $type = $field[$seq]['type'];

                                if($editRecord) {
                                    $value = $items[$i]->{$field[$seq]['flid']};

                                    switch($type) {
                                        case \App\Form::_BOOLEAN:
                                            $display = $value ? 'true' : 'false';
                                            break;
                                        case \App\Form::_MULTI_SELECT_LIST:
                                        case \App\Form::_GENERATED_LIST:
                                        case \App\Form::_ASSOCIATOR:
                                            $vals = json_decode($value);
                                            $display = implode(',',$vals);
                                            break;
                                        case \App\Form::_HISTORICAL_DATE:
                                            $dateParts = json_decode($value,true);
                                            $dateArray = [$dateParts['year']];

                                            if(!is_null($dateParts['month']) && $dateParts['month']!='') {
                                                $dateArray[] = $dateParts['month'];
                                                if(!is_null($dateParts['day']) && $dateParts['day']!='')
                                                    $dateArray[] = $dateParts['day'];
                                            }

                                            $display = implode('-',$dateArray);

                                            if(!is_null($dateParts['prefix']) && $dateParts['prefix']!='')
                                                $display = $dateParts['prefix'].' '.$display;

                                            if(!is_null($dateParts['era']) && $dateParts['era']!='')
                                                $display .= ' '.$dateParts['era'];
                                            break;
                                        default:
                                            $display = $value;
                                            break;
                                    }
                                } else {
                                    switch($type) {
                                        case \App\Form::_BOOLEAN:
                                            $value = $field[$seq]['default'][$i];
                                            $display = $value ? 'true' : 'false';
                                            break;
                                        case \App\Form::_MULTI_SELECT_LIST:
                                        case \App\Form::_GENERATED_LIST:
                                        case \App\Form::_ASSOCIATOR:
                                            $display = $field[$seq]['default'][$i];
                                            $vals = explode(',',$display);
                                            $value = json_encode($vals);
                                            break;
                                        case \App\Form::_HISTORICAL_DATE:
                                            $display = $tmpValue = $field[$seq]['default'][$i];
                                            $value = array();
                                            if(\Illuminate\Support\Str::startsWith($tmpValue, 'circa')) {
                                                $value['prefix'] = 'circa'; $tmpValue = trim(explode('circa', $tmpValue)[1]);
                                            } else if(\Illuminate\Support\Str::startsWith($tmpValue, 'pre')) {
                                                $value['prefix'] = 'pre'; $tmpValue = trim(explode('pre', $tmpValue)[1]);
                                            } else if(\Illuminate\Support\Str::startsWith($tmpValue, 'post')) {
                                                $value['prefix'] = 'post'; $tmpValue = trim(explode('post', $tmpValue)[1]);
                                            }
                                            // Era order matters here
                                            foreach(['BCE', 'KYA BP', 'CE', 'BP'] as $era) {
                                                if(\Illuminate\Support\Str::endsWith($tmpValue, $era)) {
                                                    $value['era'] = $era; $tmpValue = trim(explode($era, $tmpValue)[0]);
                                                    break;
                                                }
                                            }
                                            $year = $tmpValue;
                                            if(\Illuminate\Support\Str::contains($tmpValue, '-')) {
                                                $tmpValue = explode('-', $tmpValue);
                                                $year = $tmpValue[0];
                                                $value['month'] = $tmpValue[1];
                                                if(count($tmpValue) == 3)
                                                    $request['day'] = $tmpValue[2];
                                            }
                                            $value['year'] = $year;
                                            $value = json_encode($value);
                                            break;
                                        default:
                                            $value = $display = $field[$seq]['default'][$i];
                                            break;
                                    }
                                }
                            @endphp
                            {!! Form::hidden($flid."_combo_".$seq."[]",$value) !!}
                            <span class="combo-column combo-value">{{$display}}</span>
                        @endforeach
                    </div>
                @endfor
            @endif
        </div>

        @if(!is_null($items))
            <div class="combo-list-empty"><span class="combo-column">Add Values to Combo List Below</span></div>
        @endif

        <section class="new-object-button form-group mt-xxxl">
            <input class="open-combo-value-modal-js" type="button" value="Add a New Value" flid="{{$flid}}" typeOne="{{$oneType}}" typeTwo="{{$twoType}}">
        </section>

        <div class="modal modal-js modal-mask combo-list-modal-js">
            <div class="content">
                <div class="header">
                    <span class="title title-js">Add a New Value for {{$field['name']}}</span>
                    <a href="#" class="modal-toggle modal-toggle-js">
                        <i class="icon icon-cancel"></i>
                    </a>
                </div>
                <div class="body">
                    <span class="error-message combo-error-{{$flid}}-js"></span>
                    @foreach(['one', 'two'] as $seq)
                        <section class="combo-list-input-{{$seq}}" cfType="{{$field[$seq]['type']}}">
                            @include(
                                ${'recInput'.$seq}, ['field'=>$field[$seq], 'seq'=>$seq, 'flid'=>$flid]
                            )
                        </section>
                    @endforeach
                    <input class="btn mt-xs add-combo-value-js" type="button" value="Create Combo Value">
                </div>
            </div>
        </div>
    </div>
</div>
