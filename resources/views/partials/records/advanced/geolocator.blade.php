<section class="lat-lon-switch-js mt-m">
    <div class="form-group half mt-sm pr-m">
        {!! Form::label($flid.'_lat', (array_key_exists('alt_name', $field) && $field['alt_name']!='') ? $field['name'].' ('.$field['alt_name'].') - Latitude' : $field['name'].' - Latitude') !!}
        <div class="number-input-container number-input-container-js">
            <input type="number" class="text-input" min=-90 max=90 step=".000001" placeholder="Enter center latitude" id="{{$flid}}_lat" name="{{$flid}}_lat">
        </div>
    </div>
    <div class="form-group half mt-sm pr-l">
        {!! Form::label($flid.'_lng', 'Longitude') !!}
        <div class="number-input-container number-input-container-js">
            <input type="number" class="text-input" min=-180 max=180 step=".000001" placeholder="Enter center longitude" id="{{$flid}}_lng" name="{{$flid}}_lng">
        </div>
    </div>
</section>

<div class="form-group mt-sm">
    {!! Form::label($flid.'_range', 'Range (km)') !!}
    <div class="number-input-container number-input-container-js">
        <input type="number" class="text-input" step=".001" placeholder="Enter the search range" id="{{$flid}}_range" name="{{$flid}}_range">
    </div>
</div>
