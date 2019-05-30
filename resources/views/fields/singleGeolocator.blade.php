@extends('app-plain', ['page_title' => 'Geolocator', 'page_class' => 'field-single-geolocator'])

@section('body')
    @if(!$field['options']['Map'])
        @foreach($typedField->processDisplayData($field, $value) as $loc)
            @php
                $desc = ($loc['description'] != '') ? $loc['description'].': ' : '';
            @endphp

            @if($field['options']['DataView']=='LatLon')
                <div>{{ $desc.$loc['geometry']['location']['lat'].', '.$loc['geometry']['location']['lng'] }}</div>
            @elseif($field['options']['DataView']=='Address')
                <div>{{ $desc.$loc['formatted_address'] }}</div>
            @endif
        @endforeach
    @else
        <div id="map{{$flid}}_{{$record->kid}}" class="field-geolocator geolocator-map geolocator-map-js" map-id="{{$flid}}_{{$record->kid}}">
            @foreach($typedField->processDisplayData($field, $value) as $loc)
                <span class="geolocator-location-js hidden" loc-desc="{{$loc['description']}}"
                      loc-x="{{$loc['geometry']['location']['lat']}}"
                      loc-y="{{$loc['geometry']['location']['lng']}}"></span>
            @endforeach
        </div>
    @endif
@stop


@section('javascripts')
    @include('partials.records.javascripts')

    <script src="{{ url('assets/javascripts/vendor/leaflet/leaflet.js') }}"></script>

    <script type="text/javascript">
        var $geolocator = $('.geolocator-map-js');
        var mapID = $geolocator.attr('map-id');

        var firstLoc = $geolocator.children('.geolocator-location-js').first();
        var mapRecord = L.map('map'+mapID).setView([firstLoc.attr('loc-x'), firstLoc.attr('loc-y')], 13);
        mapRecord.scrollWheelZoom.disable();
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(mapRecord);

        var markers = [];
        $geolocator.children('.geolocator-location-js').each(function() {
            var marker = L.marker([$(this).attr('loc-x'), $(this).attr('loc-y')]).addTo(mapRecord);
            marker.bindPopup($(this).attr('loc-desc'));
            // Add marker to array to set zoom
            markers.push(marker);
        });

        // Zoom map to fit all locations
        var group = new L.featureGroup(markers);
        mapRecord.fitBounds(group.getBounds());
        mapRecord.zoomOut();
    </script>
@stop
