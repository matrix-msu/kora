@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default Value: ') !!}
        <select multiple class="multi-select default-location-js" name="default[]" data-placeholder="Add Locations Below">
            @foreach(\App\GeolocatorField::getLocationList($field) as $opt)
                <option value="{{$opt}}" selected>Description: {{explode('[Desc]',$opt)[1]}} | LatLon: {{explode('[LatLon]',$opt)[1]}} | UTM: {{explode('[UTM]',$opt)[1]}} | Address: {{explode('[Address]',$opt)[1]}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group mt-xl">
        <a href="#" class="btn half-sub-btn extend add-new-default-location-js">Create New Default Location</a>
    </div>

    <div class="form-group mt-xl">
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
    geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}';
    csrfToken = "{{ csrf_token() }}";

    Kora.Fields.Options('Geolocator');
@stop