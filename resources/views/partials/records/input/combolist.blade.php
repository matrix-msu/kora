<div class="form-group combo-value-div-js-{{$field->flid}} mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::hidden($field->flid,true) !!}

    <?php
    $oneType = \App\ComboListField::getComboFieldType($field,'one');
    $twoType = \App\ComboListField::getComboFieldType($field,'two');
    $oneName = \App\ComboListField::getComboFieldName($field,'one');
    $twoName = \App\ComboListField::getComboFieldName($field,'two');

    $defs = $field->default;
    $defArray = explode('[!def!]',$defs);
    ?>

    @if($defs!=null && $defs!='')
        @for($i=0;$i<sizeof($defArray);$i++)
            <div class="combo-value-item-js">
                @if($oneType=='Text' | $oneType=='List' | $oneType=='Number')
                    <?php $value = explode('[!f1!]',$defArray[$i])[1]; ?>
                    {!! Form::hidden($field->flid."_combo_one[]",$value) !!}
                    <span>[{{$oneName}}]: {{$value}}</span>
                @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
                    <?php
                    $valPre = explode('[!f1!]',$defArray[$i])[1];
                    $value = explode('[!]',$valPre);
                    ?>
                    {!! Form::hidden($field->flid."_combo_one[]",$valPre) !!}
                    <span>[{{$oneName}}]: {{implode(' | ',$value)}}</span>
                @endif
                <span> ~ </span>
                @if($twoType=='Text' | $twoType=='List' | $twoType=='Number')
                    <?php $value = explode('[!f2!]',$defArray[$i])[1]; ?>
                    {!! Form::hidden($field->flid."_combo_two[]",$value) !!}
                    <span>[{{$twoName}}]: {{$value}}</span>
                @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $twoType=='Associator')
                    <?php
                    $valPre = explode('[!f2!]',$defArray[$i])[1];
                    $value = explode('[!]',$valPre);
                    ?>
                    {!! Form::hidden($field->flid."_combo_two[]",$valPre) !!}
                    <span>[{{$twoName}}]: {{implode(' | ',$value)}}</span>
                @endif

                <span class="delete-combo-value-js pl-m"><a class="underline-middle-hover">[X]</a></span>
            </div>
        @endfor
    @else
        <div class="combo-list-empty">Add Values to Combo List Below</div>
    @endif
</div>

<section class="combo-list-input-one" cfName="{{$oneName}}" cfType="{{$oneType}}">
    @include('partials.fields.combo.inputs.record',['field'=>$field, 'type'=>$oneType,'cfName'=>$oneName,  'fnum'=>'one', 'flid'=>$field->flid])
</section>
<section class="combo-list-input-two" cfName="{{$twoName}}" cfType="{{$twoType}}">
    @include('partials.fields.combo.inputs.record',['field'=>$field, 'type'=>$twoType,'cfName'=>$twoName,  'fnum'=>'two', 'flid'=>$field->flid])
</section>

<section class="new-object-button form-group mt-xxxl">
    <input class="add-combo-value-js" type="button" value="Add Default Value" flid="{{$field->flid}}">
</section>