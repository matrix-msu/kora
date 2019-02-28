{!! Form::hidden('advanced',true) !!}
<div class="form-group geolocator-form-group geolocator-form-group-js mt-xxxl">
  {!! Form::label('default','Default Locations') !!}
  <div class="form-input-container">
    <p class="directions">Add Default Locations below, and order them via drag & drop or their arrow icons.</p>

    <div class="geolocator-card-container geolocator-card-container-js mb-xxl"></div>

    <section class="new-object-button">
        <input class="add-new-default-location-js" type="button" value="Create New Default Location">
    </section>
  </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('map','Map Display') !!}
    {!! Form::select('map', [0 => 'No', 1 => 'Yes'], 0, ['class' => 'single-select']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('view','Displayed Data') !!}
    {!! Form::select('view', ['LatLon' => 'Lat Long', 'Address' => 'Address'],
        'LatLon', ['class' => 'single-select']) !!}
</div>

<script>
    geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => 0, 'fid' => 0, 'flid' => 0]) }}';
    csrfToken = "{{ csrf_token() }}";
    geoListDisplay = 'LatLon';

    Kora.Fields.Options('Geolocator');
</script>
