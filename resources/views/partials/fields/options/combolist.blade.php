@extends('fields.show')

{{-- TODO::COMBO --}}
{{-- @section('presetModal')
	@include('partials.fields.fieldValuePresetModals.addComboRegexPresetModal', ['presets' => $presets])
@stop --}}

@section('fieldOptions')
    @php
        $oneType = $field['one']['type'];
        $twoType = $field['two']['type'];
        $oneName = $field['one']['name'];
        $twoName = $field['two']['name'];

        $defsOne = $field['one']['default'];
        $defsTwo = $field['two']['default'];
    @endphp

    {!! Form::hidden('typeone',$oneType) !!}
    {!! Form::hidden('typetwo',$twoType) !!}

    <div class="form-group half pr-m">
        {!! Form::label('cfname1','Combo List Field Name 1') !!}
        {!! Form::text('cfname1',$oneName, ['class' => 'text-input']) !!}
    </div>

    <div class="form-group half pl-m">
        {!! Form::label('cfname2','Combo List Field Name 2') !!}
        {!! Form::text('cfname2',$twoName, ['class' => 'text-input']) !!}
    </div>

    <section class="combo-list-options-one">
        <div class="label-spacer">
            <label>Field Options for "{{ $oneName }}"</label>
            <div class="spacer"></div>
        </div>
        @php
            $optView = $form->getFieldModel($oneType)::FIELD_OPTIONS_VIEW;
            $optParts = explode('.',$optView);
            $subView = end($optParts);
        @endphp
        @include(
            'partials.fields.options.defaults.' . $subView,
            ['field'=>$field['one'], 'seq' => 'one']
        )
    </section>

    <section class="combo-list-options-two">
        <div class="label-spacer">
            <label>Field Options for "{{ $twoName }}"</label>
            <div class="spacer"></div>
        </div>
        @php
            $optView = $form->getFieldModel($twoType)::FIELD_OPTIONS_VIEW;
            $optParts = explode('.',$optView);
            $subView = end($optParts);
        @endphp
        @include(
            'partials.fields.options.defaults.' . $subView,
            ['field'=>$field['two'], 'seq' => 'two']
        )
    </section>

    <div class="form-group mt-xxxl">
        <div class="spacer"></div>
    </div>

    @include('partials.fields.modals.addDefaultValue')
    <section class="combo-list-defaults">
        {!! Form::label('default', 'Default Combo List Values') !!}
        <div class="container">
            <div class="form-group combo-list-display combo-value-div-js {{ is_null($defsOne) ? 'hidden' : '' }}">
                    <div class="combo-list-title">
                        <span class="combo-column combo-title">{{$oneName}}</span>
                        <span class="combo-column combo-title">{{$twoName}}</span>
                    </div>

                @if(!is_null($defsOne))
                    @for($i=0;$i<count($defsOne);$i++)
                        @php
                            $valueOne = $defsOne[$i];
                            $valueTwo = $defsTwo[$i];
                        @endphp
                        <div class="card combo-value-item-js">
                            @if($oneType=='Text' | $oneType=='List' | $oneType=='Integer'| $oneType=='Float' | $oneType=='Boolean')
                                {!! Form::hidden("default_combo_one[]",$valueOne) !!}
                                @php
                                    if($oneType=='Boolean')
                                        if($valueOne == 1)
                                            $valueOne = 'true';
                                        else if($valueOne == 0)
                                            $valueOne = 'false';
                                @endphp
                                <span class="combo-column">{{$valueOne}}</span>
                            @elseif($oneType=='Date' | $oneType=='Historical Date')
                                @php
                                $date = [$valueOne['month'], $valueOne['day'], $valueOne['year']];
                                @endphp
                                {!! Form::hidden("default_day_combo_one[]",$valueOne['day']) !!}
                                {!! Form::hidden("default_month_combo_one[]",$valueOne['month']) !!}
                                {!! Form::hidden("default_year_combo_one[]",$valueOne['year']) !!}
                                @if($oneType=='Historical Date')
                                    @php
                                        array_push(
                                            $date,
                                            $valueOne['prefix'],
                                            $valueOne['era']
                                        );
                                    @endphp
                                    {!! Form::hidden("default_prefix_combo_one[]",$valueOne['prefix']) !!}
                                    {!! Form::hidden("default_era_combo_one[]",$valueOne['era']) !!}
                                @endif
                                <span class="combo-column">{{implode('/', array_filter($date))}}</span>
                            @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
                                {!! Form::hidden("default_combo_one[]",json_encode($valueOne)) !!}
                                <span class="combo-column">{{implode(' | ',$valueOne)}}</span>
                            @endif
                            @if($twoType=='Text' | $twoType=='List' | $oneType=='Integer'| $oneType=='Float' | $twoType=='Boolean')
                                {!! Form::hidden("default_combo_two[]",$valueTwo) !!}
                                @php
                                    if($twoType=='Boolean')
                                        if($valueTwo == 1)
                                            $valueTwo = 'true';
                                        else if($valueTwo == 0)
                                            $valueTwo = 'false';
                                @endphp
                                <span class="combo-column">{{$valueTwo}}</span>
                            @elseif($twoType=='Date' | $twoType=='Historical Date')
                                @php
                                    $date = [$valueTwo['month'], $valueTwo['day'], $valueTwo['year']];
                                @endphp
                                {!! Form::hidden("default_day_combo_two[]",$valueTwo['day']) !!}
                                {!! Form::hidden("default_month_combo_two[]",$valueTwo['month']) !!}
                                {!! Form::hidden("default_year_combo_two[]",$valueTwo['year']) !!}
                                @if($twoType=='Historical Date')
                                    @php
                                        array_push(
                                            $date,
                                            $valueTwo['prefix'],
                                            $valueTwo['era']
                                        );
                                    @endphp
                                    {!! Form::hidden("default_prefix_combo_two[]",$valueTwo['prefix']) !!}
                                    {!! Form::hidden("default_era_combo_two[]",$valueTwo['era']) !!}
                                @endif
                                <span class="combo-column">{{implode('/', array_filter($date))}}</span>
                            @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $twoType=='Associator')
                                {!! Form::hidden("default_combo_two[]",json_encode($valueTwo)) !!}
                                <span class="combo-column">{{implode(' | ', $valueTwo)}}</span>
                            @endif

                            <span class="combo-delete delete-combo-value-js">
								<a class="quick-action delete-option delete-default-js tooltip" tooltip="Delete Default Value">
									<i class="icon icon-trash"></i>
								</a>
							</span>
                        </div>
                    @endfor
                @endif

            </div>

            <section class="new-object-button form-group">
                <input class="combolist-add-new-list-value-modal-js {{ is_null($defsOne) ? '' : 'mt-xl' }}" type="button" value="Add a new Default Value">
            </section>
        </div>
    </section>
@stop

@section('fieldOptionsJS')
    assocSearchURI = "{{ action('AssociatorSearchController@assocSearch',['pid' => $form->project_id,'fid'=>$form->id, 'flid'=>$flid]) }}";
    csrfToken = "{{ csrf_token() }}";
    type1 = '{{$oneType}}';
    type2 = '{{$twoType}}';
    name1 = '{{$oneName}}';
    name2 = '{{$twoName}}';

    Kora.Fields.Options('Combo List');
@stop
