@extends('fields.show')

@section('fieldOptions')
    @php
        $oneType = $field['one']['type'];
        $twoType = $field['two']['type'];
        $oneName = $field['one']['name'];
        $twoName = $field['two']['name'];

        $defsOne = $field['one']['default'];
        $defsTwo = $field['two']['default'];

        $optView = $form->getFieldModel($oneType)::FIELD_OPTIONS_VIEW;
        $optParts = explode('.',$optView);
        $subViewOne = end($optParts);

        $optView = $form->getFieldModel($twoType)::FIELD_OPTIONS_VIEW;
        $optParts = explode('.',$optView);
        $subViewTwo = end($optParts);
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
        @if(\Illuminate\Support\Facades\View::exists('partials.fields.options.config.' . $subViewOne))
            @include(
                'partials.fields.options.config.' . $subViewOne,
                ['field'=>$field['one'], 'seq' => 'one']
            )
        @endif
    </section>

    <section class="combo-list-options-two">
        <div class="label-spacer">
            <label>Field Options for "{{ $twoName }}"</label>
            <div class="spacer"></div>
        </div>
        @if(\Illuminate\Support\Facades\View::exists('partials.fields.options.config.' . $subViewTwo))
            @include(
                'partials.fields.options.config.' . $subViewTwo,
                ['field'=>$field['two'], 'seq' => 'two']
            )
        @endif
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
                            {!! Form::hidden("default_combo_one[]",$valueOne) !!}
                            <span class="combo-column">{{$valueOne}}</span>
                            {!! Form::hidden("default_combo_two[]",$valueTwo) !!}
                            <span class="combo-column">{{$valueTwo}}</span>

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
