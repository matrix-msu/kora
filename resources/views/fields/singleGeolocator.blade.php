@extends('app-plain', ['page_title' => 'Geolocator', 'page_class' => 'field-single-geolocator'])

@section('body')
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
        <div id="map{{$field->flid}}_{{$record->rid}}" class="field-geolocator geolocator-map geolocator-map-js" map-id="{{$field->flid}}_{{$record->rid}}">
            @foreach( App\GeolocatorField::locationsToOldFormat($typedField->locations()->get()) as $location)
                <span class="geolocator-location-js hidden" loc-desc="{{explode('[Desc]',$location)[1]}}"
                      loc-x="{{explode(',', explode('[LatLon]',$location)[1])[0]}}"
                      loc-y="{{explode(',', explode('[LatLon]',$location)[1])[1]}}"></span>
            @endforeach
        </div>
    @endif
@stop


@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ config('app.url') }}assets/javascripts/vendor/leaflet/leaflet.js"></script>

    <script type="text/javascript">
        var $geolocator = $('.geolocator-map-js');
        var mapID = $geolocator.attr('map-id');

        var firstLoc = $geolocator.children('.geolocator-location-js').first();
        var mapRecord = L.map('map'+mapID).setView([firstLoc.attr('loc-x'), firstLoc.attr('loc-y')], 13);
        mapRecord.scrollWheelZoom.disable();
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(mapRecord);

        $geolocator.children('.geolocator-location-js').each(function() {
            var marker = L.marker([$(this).attr('loc-x'), $(this).attr('loc-y')]).addTo(mapRecord);
            marker.bindPopup($(this).attr('loc-desc'));
        });
    </script>
@stop
