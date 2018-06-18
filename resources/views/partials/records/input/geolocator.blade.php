<?php
    if($editRecord && $hasData) {
        $selected = App\GeolocatorField::locationsToOldFormat($typedField->locations()->get());
        $listOpts = array();
        foreach($selected as $val){
            $listOpts[$val] = 'Description: '.explode('[Desc]',$val)[1].' | LatLon: '.explode('[LatLon]',$val)[1].' | UTM: '.explode('[UTM]',$val)[1].' | Address: '.explode('[Address]',$val)[1];
        }
    } else if($editRecord) {
        $selected = null;
        $listOpts = array();
    } else {
        $selected = explode('[!]',$field->default);
        $listOpts = \App\GeolocatorField::getLocationList($field);
    }
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    <span class="error-message"></span>
    {!! Form::select($field->flid.'[]', $listOpts, $selected, ['class' => 'multi-select '.$field->flid.'-location-js preset-clear-chosen-js',
        'Multiple', 'data-placeholder' => "Add Locations Below", 'id' => 'list'.$field->flid]) !!}
</div>

<section class="new-object-button form-group mt-xl">
    <input flid="{{$field->flid}}" type="button" class="add-new-default-location-js" value="Create New Location">
</section>