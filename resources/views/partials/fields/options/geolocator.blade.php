@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addLocationPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createLocationPresetModal')
@stop

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default Value: ') !!}
        <select multiple class="multi-select default-location-js" name="default[]" data-placeholder="Add Locations Below">
            @foreach(\App\GeolocatorField::getLocationList($field) as $opt)
                <option value="{{$opt}}" selected>Description: {{explode('[Desc]',$opt)[1]}} | LatLon: {{explode('[LatLon]',$opt)[1]}} | UTM: {{explode('[UTM]',$opt)[1]}} | Address: {{explode('[Address]',$opt)[1]}}</option>
            @endforeach
        </select>
    </div>

    <section class="new-object-button low-margin form-group">
        <input type="button" class="add-new-default-location-js" value="Create New Default Location">
        <div><a href="#" class="field-preset-link open-location-modal-js">Use a Value Preset for these Locations</a></div>
        <div class="open-create-regex"><a href="#" class="field-preset-link open-create-location-modal-js right
            @if(empty(\App\GeolocatorField::getLocationList($field))) disabled tooltip @endif" tooltip="You must submit or update the field before creating a New Value Preset">
                Create a New Value Preset from these Locations</a></div>
    </section>

    <div class="form-group mt-xxxl">
        {!! Form::label('map','Map Display: ') !!}
        {!! Form::select('map', ['No' => 'No','Yes' => 'Yes'],
            \App\Http\Controllers\FieldController::getFieldOption($field,'Map'), ['class' => 'single-select']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('view','Displayed Data: ') !!}
        {!! Form::select('view', ['LatLon' => 'Lat Long','UTM' => 'UTM Coordinates','Textual' => 'Address'],
            \App\Http\Controllers\FieldController::getFieldOption($field,'DataView'), ['class' => 'single-select']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    csrfToken = "{{ csrf_token() }}";

    Kora.Fields.Options('Geolocator');
@stop