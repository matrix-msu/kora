<div class="form-group combo-value-div-js-{{$flid}} mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::hidden($flid, true, ['id' => $flid]) !!}

    <?php
    $oneType = $field['one']['type'];
    $twoType = $field['two']['type'];
    $oneName = $field['one']['name'];
    $twoName = $field['two']['name'];
    $oneFlid = $field['one']['flid'];
    $twoFlid = $field['two']['flid'];

    if($editRecord) {
        // TODO::@andrew.joye need a function to get values
        $items = $typedField->retrieve($flid, $form->id, $record->{$flid});
    } else {
        $items = $field['one']['default'];
    }
    ?>

    <div class="combo-list-display combo-list-display-js preset-clear-combo-js">
        <div class="mb-sm">
            <span class="combo-column combo-title">{{$oneName}}</span>
            <span class="combo-column combo-title">{{$twoName}}</span>
        </div>

        <div class="combo-value-item-container-js">
            @if(!is_null($items))
                @for($i=0;$i<count($items);$i++)
                    @php
                        if($editRecord) {
                            $valueOne = $items[$i]->{$oneFlid};
                            $valueTwo = $items[$i]->{$twoFlid};
                        } else {
                            $valueOne = $field['one']['default'][$i];
                            $valueTwo = $field['two']['default'][$i];
                        }
                    @endphp
                    <div class="combo-value-item combo-value-item-js">
                        <span class="combo-delete delete-combo-value-js tooltip" tooltip="Delete Combo Value"><i class="icon icon-trash"></i></span>

                        @if($oneType=='Text' | $oneType=='List' | $oneType=='Integer' | $oneType=='Float' | $oneType=='Date')
                            {!! Form::hidden($flid."_combo_one[]",$valueOne) !!}
                            <span class="combo-column combo-value">{{$valueOne}}</span>
                        @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
                            {!! Form::hidden($flid."_combo_one[]",$valueOne) !!}
                            <span class="combo-column combo-value">{{implode(' | ',$valueOne)}}</span>
                        @endif

                        @if($twoType=='Text' | $twoType=='List' | $oneType=='Integer' | $oneType=='Float' | $twoType=='Date')
                            {!! Form::hidden($flid."_combo_two[]",$valueTwo) !!}
                            <span class="combo-column combo-value">{{$valueTwo}}</span>
                        @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $twoType=='Associator')
                            {!! Form::hidden($flid."_combo_two[]",$valueTwo) !!}
                            <span class="combo-column combo-value">{{implode(' | ',$valueTwo)}}</span>
                        @endif
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

                    <section class="combo-list-input-one" cfType="{{$oneType}}">
                        @include('partials.fields.combo.inputs.record',['field'=>$field, 'type'=>$oneType,'cfName'=>$oneName,  'fnum'=>'one', 'flid'=>$flid])
                    </section>
                    <section class="combo-list-input-two" cfType="{{$twoType}}">
                        @include('partials.fields.combo.inputs.record',['field'=>$field, 'type'=>$twoType,'cfName'=>$twoName,  'fnum'=>'two', 'flid'=>$flid])
                    </section>
                    <input class="btn mt-xs add-combo-value-js" type="button" value="Create Combo Value">
                </div>
            </div>
        </div>
    </div>
</div>
