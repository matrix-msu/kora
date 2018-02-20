<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::select($field->flid.'[]',\App\GeolocatorField::getLocationList($field), explode('[!]',$field->default),
        ['class' => 'multi-select '.$field->flid.'-location-js', 'Multiple', 'data-placeholder' => "Add Locations Below"]) !!}
</div>

<section class="new-object-button form-group mt-xl">
    <input flid="{{$field->flid}}" type="button" class="add-new-default-location-js" value="Create New Location">
</section>