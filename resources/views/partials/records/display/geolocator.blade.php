@if(\App\Http\Controllers\FieldController::getFieldOption($field,'Map')=='No')
    @foreach( App\GeolocatorField::locationsToOldFormat($typedField->locations()->get()) as $opt)
        @if(\App\Http\Controllers\FieldController::getFieldOption($field,'DataView')=='LatLon')
            <div>{{ explode('[Desc]',$opt)[1].': '.explode('[LatLon]',$opt)[1] }}</div>
        @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'DataView')=='UTM')
            <div>{{ explode('[Desc]',$opt)[1].': '.explode('[UTM]',$opt)[1] }}</div>
        @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'DataView')=='Textual')
            <div>{{ explode('[Desc]',$opt)[1].': '.explode('[Address]',$opt)[1] }}</div>
        @endif
    @endforeach
@else
    <div id="map{{$field->flid}}_{{$record->rid}}" class="geolocator-map geolocator-map-js mt-xxs" map-id="{{$field->flid}}_{{$record->rid}}">
        @foreach( App\GeolocatorField::locationsToOldFormat($typedField->locations()->get()) as $location)
            <span class="geolocator-location-js hidden" loc-desc="{{explode('[Desc]',$location)[1]}}"
                  loc-x="{{explode(',', explode('[LatLon]',$location)[1])[0]}}"
                  loc-y="{{explode(',', explode('[LatLon]',$location)[1])[1]}}"></span>
        @endforeach
    </div>
@endif