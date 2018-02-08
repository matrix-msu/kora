<div class="form-group mt-xl">
    {!! Form::label('default','Default Value: ') !!}
    <select multiple class="multi-select default-location-js" name="default[]"
        data-placeholder="Add Locations Below"></select>
</div>

<div class="form-group mt-xl">
    <a href="#" class="btn half-sub-btn extend add-new-default-location-js">Create New Default Location</a>
</div>

<div class="form-group mt-xl">
    {!! Form::label('map','Map Display: ') !!}
    {!! Form::select('map', ['No' => 'No','Yes' => 'Yes'], 'No', ['class' => 'single-select']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('view','Displayed Data: ') !!}
    {!! Form::select('view', ['LatLon' => 'Lat Long','UTM' => 'UTM Coordinates','Textual' => 'Address'],
        'LatLon', ['class' => 'single-select']) !!}
</div>

<script>
    geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => 0, 'fid' => 0, 'flid' => 0]) }}';
    csrfToken = "{{ csrf_token() }}";

    Kora.Fields.Options('Geolocator');
</script>