<div class="form-group combo-value-div-js-{{$flid}} mt-xxxl">
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

    if($editRecord) {
        $items = $typedField->retrieve($flid, $form->id, $record->{$flid});
    } else {
        $items = $field['one']['default'];
    }
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
                        <span class="combo-delete delete-combo-value-js tooltip" tooltip="Delete Combo Value"><i class="icon icon-trash"></i></span>
                        @foreach (['one', 'two'] as $seq)
                            @php
                                $value = $display = null;
                                $type = $field[$seq]['type'];

                                if($editRecord) {
                                    $value = $display = $items[$i]->{$field[$seq]['flid']};

                                    //if($type == 'Historical Date') { //TODO::COMBO_FINISH
                                    //    $tmp = json_decode($value, true);
                                    //    $display = implode(
                                    //        '-',
                                    //        array_filter([
                                    //            $tmp['year'],
                                    //            $tmp['month'],
                                    //            $tmp['day']
                                    //        ])
                                    //    );
                                    //}
                                } else {
                                    $value = $display = $field[$seq]['default'][$i];

                                    //if(in_array($type, ['Date', 'Historical Date'])) { //TODO::COMBO_FINISH
                                    //    $display = implode(
                                    //        '-',
                                    //        array_filter([
                                    //            $value['year'],
                                    //            $value['month'],
                                    //            $value['day']
                                    //        ])
                                    //    );
                                    //
                                    //    if($type == 'Historical Date') {
                                    //        $value = json_encode($value);
                                    //    } else {
                                    //        $value = $display;
                                    //    }
                                    //}
                                }

                                //if(in_array($type, ['Multi-Select List', 'Generated List', 'Associator'])) { //TODO::COMBO_FINISH
                                //    if(is_array($value))
                                //        $value = json_encode($value);
                                //    $display = implode(', ', json_decode($value));
                                //}
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
