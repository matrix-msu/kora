@extends('fields.show')

@section('presetModal')
    @include('partials.fields.fieldValuePresetModals.addLocationPresetModal', ['presets' => $presets])
    @include('partials.fields.fieldValuePresetModals.createLocationPresetModal')
@stop

@section('fieldOptions')
    <div class="form-group geolocator-form-group geolocator-form-group-js geolocator-{{$field->flid}}-js mt-xxxl">
        {!! Form::label('default','Default Locations') !!}
        <div class="form-input-container">
            <p class="directions">Add Default Locations below, and order them via drag & drop or their arrow icons.</p>

            <div class="geolocator-card-container geolocator-card-container-js mb-xxl">
                @foreach(\App\GeolocatorField::getLocationList($field) as $opt)
                    <?php
                        $desc = explode('[Desc]',$opt)[1];
                        $latlon = implode(', ', explode(',', explode('[LatLon]',$opt)[1]));
                        $utm = explode('[UTM]',$opt)[1];
                        $address = explode('[Address]',$opt)[1];
                    ?>
                    <div class="card geolocator-card geolocator-card-js">
                        <input type="hidden" class="list-option-js" name="default[]" value="{{$opt}}'">
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
                                <span class="title">{{$desc}}</span>
                            </div>
                            <div class="card-toggle-wrap">
                                <a class="geolocator-delete geolocator-delete-js tooltip" tooltip="Delete Location" href=""><i class="icon icon-trash"></i></a>
                            </div>
                        </div>
                        <div class="content"><p class="location"><span class="bold">LatLon:</span> {{$latlon}}</p></div>
                    </div>
                @endforeach
            </div>

            <section class="new-object-button">
                <input class="add-new-default-location-js" type="button" value="Create New Default Location">
            </section>
        </div>
    </div>

    <section class="form-group">
        <div><a href="#" class="field-preset-link open-location-modal-js">Use a Value Preset for these Locations</a></div>
        <div class="open-create-regex"><a href="#" class="field-preset-link open-create-location-modal-js right
            @if(empty(\App\GeolocatorField::getLocationList($field))) disabled tooltip @endif" tooltip="You must submit or update the field before creating a New Value Preset">
                Create a New Value Preset from these Locations</a></div>
    </section>

    <div class="form-group mt-xxxl">
        {!! Form::label('map','Map Display') !!}
        {!! Form::select('map', ['No' => 'No','Yes' => 'Yes'],
            \App\Http\Controllers\FieldController::getFieldOption($field,'Map'), ['class' => 'single-select']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('view','Displayed Data') !!}
        {!! Form::select('view', ['LatLon' => 'Lat Long','UTM' => 'UTM Coordinates','Textual' => 'Address'],
            \App\Http\Controllers\FieldController::getFieldOption($field,'DataView'), ['class' => 'single-select']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}';
    csrfToken = "{{ csrf_token() }}";

    Kora.Fields.Options('Geolocator');
@stop
