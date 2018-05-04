@extends('fields.show')

@section('fieldOptions')
    <?php
    $oneType = \App\ComboListField::getComboFieldType($field,'one');
    $twoType = \App\ComboListField::getComboFieldType($field,'two');
    $oneName = \App\ComboListField::getComboFieldName($field,'one');
    $twoName = \App\ComboListField::getComboFieldName($field,'two');

    $defs = $field->default;
    $defArray = explode('[!def!]',$defs);
    ?>

    {!! Form::hidden('typeone',$oneType) !!}
    {!! Form::hidden('typetwo',$twoType) !!}
    {!! Form::hidden('nameone',$oneName) !!}
    {!! Form::hidden('nametwo',$twoName) !!}

    <div class="form-group half pr-m">
        {!! Form::label('nameone','Combo List Field Name 1: ') !!}
        {!! Form::text('nameone',$oneName, ['class' => 'text-input']) !!}
    </div>

    <div class="form-group half pl-m">
        {!! Form::label('nametwo','Combo List Field Name 2: ') !!}
        {!! Form::text('nametwo',$twoName, ['class' => 'text-input']) !!}
    </div>

    <div class="form-group mt-xxxl">
        <div class="spacer"></div>
    </div>

    <section class="combo-list-defaults">
        <div class="form-group combo-list-display combo-value-div-js">
            {!! Form::label('default', 'Default: ') !!}
            <div>
                <span class="combo-column combo-title">{{$oneName}}</span>
                <span class="combo-column combo-title">{{$twoName}}</span>
            </div>
            @if($defs!=null && $defs!='')
                @for($i=0;$i<sizeof($defArray);$i++)
                    <div class="combo-value-item-js">
                        @if($oneType=='Text' | $oneType=='List' | $oneType=='Number')
                            <?php $value = explode('[!f1!]',$defArray[$i])[1]; ?>
                            {!! Form::hidden("default_combo_one[]",$value) !!}
                            <span class="combo-column">{{$value}}</span>
                        @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
                            <?php
                            $valPre = explode('[!f1!]',$defArray[$i])[1];
                            $value = explode('[!]',$valPre);
                            ?>
                            {!! Form::hidden("default_combo_one[]",$valPre) !!}
                            <span class="combo-column">{{implode(' | ',$value)}}</span>
                        @endif

                        @if($twoType=='Text' | $twoType=='List' | $twoType=='Number')
                            <?php $value = explode('[!f2!]',$defArray[$i])[1]; ?>
                            {!! Form::hidden("default_combo_two[]",$value) !!}
                            <span class="combo-column">{{$value}}</span>
                        @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $twoType=='Associator')
                            <?php
                            $valPre = explode('[!f2!]',$defArray[$i])[1];
                            $value = explode('[!]',$valPre);
                            ?>
                            {!! Form::hidden("default_combo_two[]",$valPre) !!}
                            <span class="combo-column">{{implode(' | ',$value)}}</span>
                        @endif

                        <span class="combo-delete delete-combo-value-js"><a class="underline-middle-hover">[X]</a></span>
                    </div>
                @endfor
            @else
                <div class="combo-list-empty"><span class="combo-column">Add Values to Combo List Below</span></div>
            @endif
        </div>
    </section>


    <section class="combo-list-input-one">
        @include('partials.fields.combo.inputs.defaults',['field'=>$field, 'type'=>$oneType, 'cfName'=>$oneName, 'fnum'=>'one'])
    </section>
    <section class="combo-list-input-two">
        @include('partials.fields.combo.inputs.defaults',['field'=>$field, 'type'=>$twoType, 'cfName'=>$twoName, 'fnum'=>'two'])
    </section>

    <section class="new-object-button form-group mt-xxxl">
        <input class="add-combo-value-js" type="button" value="Create new Default value">
    </section>

    <div class="form-group mt-xxxl">
        <div class="spacer"></div>
    </div>

    <section class="combo-list-options-one">
        <h4>Field Options for {{ $oneName }}</h4>
        @if($oneType=='Text')
            @include('partials.fields.combo.options.text',['field'=>$field,'fnum'=>'one'])
        @elseif($oneType=='Number')
            @include('partials.fields.combo.options.number',['field'=>$field,'fnum'=>'one'])
        @elseif($oneType=='List')
            @include('partials.fields.combo.options.list',['field'=>$field,'fnum'=>'one'])
        @elseif($oneType=='Multi-Select List')
            @include('partials.fields.combo.options.mslist',['field'=>$field,'fnum'=>'one'])
        @elseif($oneType=='Generated List')
            @include('partials.fields.combo.options.genlist',['field'=>$field,'fnum'=>'one'])
        @elseif($oneType=='Associator')
            @include('partials.fields.combo.options.associator',['field'=>$field,'fnum'=>'one'])
        @endif
    </section>

    <div class="form-group mt-xxxl">
        <div class="spacer"></div>
    </div>

    <section class="combo-list-options-two">
        <h4>Field Options for {{ $twoName }}</h4>
        @if($twoType=='Text')
            @include('partials.fields.combo.options.text',['field'=>$field,'fnum'=>'two'])
        @elseif($twoType=='Number')
            @include('partials.fields.combo.options.number',['field'=>$field,'fnum'=>'two'])
        @elseif($twoType=='List')
            @include('partials.fields.combo.options.list',['field'=>$field,'fnum'=>'two'])
        @elseif($twoType=='Multi-Select List')
            @include('partials.fields.combo.options.mslist',['field'=>$field,'fnum'=>'two'])
        @elseif($twoType=='Generated List')
            @include('partials.fields.combo.options.genlist',['field'=>$field,'fnum'=>'two'])
        @elseif($twoType=='Associator')
            @include('partials.fields.combo.options.associator',['field'=>$field,'fnum'=>'two'])
        @endif
    </section>

    {{--//TODO::PRESETS--}}
@stop

@section('fieldOptionsJS')
    assocSearchURI = "{{ action('AssociatorSearchController@assocSearch',['pid' => $field->pid,'fid'=>$field->fid, 'flid'=>$field->flid]) }}";
    csrfToken = "{{ csrf_token() }}";
    type1 = '{{$oneType}}';
    type2 = '{{$twoType}}';
    name1 = '{{$oneName}}';
    name2 = '{{$twoName}}';

    Kora.Fields.Options('Combo List');
@stop