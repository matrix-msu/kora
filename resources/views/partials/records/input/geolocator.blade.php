@php
    if($editRecord && !is_null($record->{$flid})) {
        $locations = json_decode($record->{$flid},true);
    } else {
        $locations = $field['default'];
    }

    $dataView = $field['options']['DataView'];
@endphp

<div class="form-group geolocator-form-group geolocator-form-group-js geolocator-{{$flid}}-js mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>

    <div class="form-input-container">
        <p class="directions">Add Default Locations below, and order them via drag & drop or their arrow icons.</p>

        <div class="geolocator-card-container geolocator-card-container-js mb-xxl">
            @if (!is_null($locations))
                @foreach($locations as $loc)
                    @php
                        $desc = $loc['description'];
                        $latlon = $loc['geometry']['location']['lat'].', '.$loc['geometry']['location']['lng'];
                        $address = $loc['formatted_address'];
                        $finalResult = json_encode($loc);
                    @endphp
                    <div class="card geolocator-card geolocator-card-js">
                        <input type="hidden" class="list-option-js" name="{{$flid}}[]" value="{{$finalResult}}">
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
                        @if($dataView == 'LatLon')
                            <div class="content"><p class="location"><span class="bold">LatLon:</span> {{$latlon}}</p></div>
                        @elseif($dataView == 'Address')
                            <div class="content"><p class="location"><span class="bold">Address:</span> {{$address}}</p></div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        <section class="new-object-button">
            <input class="add-new-default-location-js" type="button" value="Create New Location" flid="{{$flid}}" display-type="{{$dataView}}">
        </section>
    </div>
</div>
