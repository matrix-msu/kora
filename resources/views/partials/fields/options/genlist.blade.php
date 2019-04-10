@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addListPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createListPresetModal')
@stop

@section('fieldOptions')
    <div class="form-group specialty-field-group list-input-form-group">
        {!! Form::label('options','List Options') !!}

        <div class="form-input-container">
            <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

            <!-- Cards of list options -->
            <div class="list-option-card-container list-option-card-container-js">
                @foreach($field['options']['Options'] as $option)
                    <div class="card list-option-card list-option-card-js" data-list-value="{{ $option }}">
                        <input type="hidden" class="list-option-js" name="options[]" value="{{ $option }}">

                        <div class="header">
                            <div class="left">
                                <div class="move-actions">
                                    <a class="action move-action-js up-js" href="">
                                        <i class="icon icon-arrow-up"></i>
                                    </a>

                                    <a class="action move-action-js down-js" href="">
                                        <i class="icon icon-arrow-down"></i>
                                    </a>
                                </div>

                                <span class="title">{{ $option }}</span>
                            </div>

                            <div class="card-toggle-wrap">
                                <a class="list-option-delete list-option-delete-js tooltip" href="" tooltip="Delete List Option"><i class="icon icon-trash"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Card to add list options -->
            <div class="card new-list-option-card new-list-option-card-js">
                <div class="header">
                    <div class="left">
                        <input class="new-list-option new-list-option-js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options'>
                    </div>

                    <div class="card-toggle-wrap">
                        <a class="list-option-add list-option-add-js" href=""><span>Add</span></a>
                    </div>
                </div>
            </div>
        </div>

        <div><a href="#" class="field-preset-link open-list-modal-js">Use a Value Preset for these List Options</a></div>
        <div class="open-create-regex"><a href="#" class="field-preset-link open-create-list-modal-js right
            @if(empty($field['options']['Options'])) disabled tooltip @endif" tooltip="You must submit or update the field before creating a New Value Preset">
                Create a New Value Preset from these List Options</a></div>
    </div>

    <div class="form-group mt-xxxl">
        {!! Form::label('default','Default') !!}
        {!! Form::select('default[]',App\KoraFields\GeneratedListField::getList($field), $field['default'],
        ['class' => 'multi-select list-default-js', 'multiple', 'data-placeholder' => 'Select the default values here (Values must be added above in order to select)']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('regex','Regex') !!}
        {!! Form::text('regex', $field['options']['Regex'], ['class' => 'text-input', 'placeholder' => 'Enter regular expression pattern here']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Generated List');
@stop
