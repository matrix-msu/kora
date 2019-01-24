<section class="lat-lon-switch-js">
    <div class="form-group half pr-m">
        {!! Form::label($field->flid.'_lat', 'Latitude') !!}
        <div class="number-input-container number-input-container-js">
            <input type="number" class="text-input" min=-90 max=90 step=".000001" placeholder="Enter center latitude" id="{{$field->flid}}_lat" name="{{$field->flid}}_lat">
        </div>
    </div>
    <div class="form-group half pr-l">
        {!! Form::label($field->flid.'_lon', 'Longitude') !!}
        <div class="number-input-container number-input-container-js">
            <input type="number" class="text-input" min=-180 max=180 step=".000001" placeholder="Enter center longitude" id="{{$field->flid}}_lon" name="{{$field->flid}}_lon">
        </div>
    </div>
</section>

<div class="form-group mt-sm">
    {!! Form::label($field->flid.'_range', 'Range (km)') !!}
    <div class="number-input-container number-input-container-js">
        <input type="number" class="text-input" step=".001" placeholder="Enter the search range" id="{{$field->flid}}_range" name="{{$field->flid}}_range">
    </div>
</div>