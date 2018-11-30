<?php
    if($editRecord && $hasData) {
        $selected = App\GeolocatorField::locationsToOldFormat($typedField->locations()->get());
        $listOpts = $selected;
    } else if($editRecord) {
        $selected = null;
        $listOpts = array();
    } else {
        $selected = explode('[!]',$field->default);
        $listOpts = \App\GeolocatorField::getLocationList($field);
    }
?>

<div class="form-group geolocator-form-group geolocator-form-group-js geolocator-{{$field->flid}}-js mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>
    
    <div class="form-input-container">
        <p class="directions">Add Default Locations below, and order them via drag & drop or their arrow icons.</p>

        <div class="geolocator-card-container geolocator-card-container-js mb-xxl">
            @foreach($listOpts as $opt)
                <?php
                $desc = (array_key_exists(1, explode('[Desc]',$opt)) ? explode('[Desc]',$opt)[1] : '');
                $latlon = (array_key_exists(1, explode('[LatLon]',$opt)) ? implode(', ', explode(',', explode('[LatLon]',$opt)[1])) : '');
                $utm = (array_key_exists(1, explode('[UTM]',$opt)) ? explode('[UTM]',$opt)[1] : '');
                $address = (array_key_exists(1, explode('[Address]',$opt)) ? explode('[Address]',$opt)[1] : '');
                ?>
                <div class="card geolocator-card geolocator-card-js">
                    <input type="hidden" class="list-option-js" name="{{$field->flid}}[]" value="{{$opt}}'">
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
            <input class="add-new-default-location-js" type="button" value="Create New Location" flid="{{$field->flid}}">
        </section>
    </div>
</div>
