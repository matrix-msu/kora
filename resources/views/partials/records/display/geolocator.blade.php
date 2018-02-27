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
    <div id="map{{$field->flid}}" style="height:270px;"></div>
    <?php $locs = array(); ?>
    @foreach( App\GeolocatorField::locationsToOldFormat($typedField->locations()->get()) as $location)
        <?php
        $loc = array();
        $desc = explode('[Desc]',$location)[1];
        $x = explode(',', explode('[LatLon]',$location)[1])[0];
        $y = explode(',', explode('[LatLon]',$location)[1])[1];

        $loc['desc'] = $desc;
        $loc['x'] = $x;
        $loc['y'] = $y;

        array_push($locs,$loc);
        ?>
    @endforeach
    <script>
        var map{{$field->flid}} = L.map('map{{$field->flid}}').setView([{{$locs[0]['x']}}, {{$locs[0]['y']}}], 13);
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(map{{$field->flid}});
                @foreach($locs as $loc)
        var marker = L.marker([{{$loc['x']}}, {{$loc['y']}}]).addTo(map{{$field->flid}});
        marker.bindPopup("{{$loc['desc']}}");
        @endforeach
    </script>
@endif