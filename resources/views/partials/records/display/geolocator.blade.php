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
        <div class="field-sidebar">
            <div class="top">
                <div class="field-btn external-button-js">
                    <i class="icon icon-external-link"></i>
                </div>
            </div>

            <div class="bottom">
                <div class="field-btn full-screen-button-js">
                    <i class="icon icon-maximize"></i>
                </div>
            </div>
        </div>

        @foreach( App\GeolocatorField::locationsToOldFormat($typedField->locations()->get()) as $location)
            <span class="geolocator-location-js hidden" loc-desc="{{explode('[Desc]',$location)[1]}}"
                  loc-x="{{explode(',', explode('[LatLon]',$location)[1])[0]}}"
                  loc-y="{{explode(',', explode('[LatLon]',$location)[1])[1]}}"></span>
        @endforeach

        <div class="modal modal-js modal-mask geolocator-map-modal geolocator-map-modal-js">
            <div class="content">
                <div class="header">
                    <a href="#" class="modal-toggle modal-toggle-js">
                        <i class="icon icon-cancel"></i>
                    </a>
                </div>
                <div class="body">
                    <div id="modalmap{{$field->flid}}_{{$record->rid}}" class="geolocator-modal-map geolocator-modal-map-js">

                    </div>
                </div>
            </div>
        </div>
    </div>
@endif