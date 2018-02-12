<div class="form-group mt-xxxl">
    {!! Form::label('default','Default Value: ') !!}
    <select multiple class="multi-select default-location-js" name="default[]"
        data-placeholder="Add Locations Below"></select>
</div>

<form class="new-object-button form-group mt-xl">
    <input type="button" class="add-new-default-location-js" value="Create New Default Location">
</form>

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