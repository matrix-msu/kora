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
    <div class="sidebar-container">
        <div class="field-sidebar field-sidebar-js">
            <div class="top">
                <a target="_blank" href="{{ action('FieldAjaxController@singleGeolocator', ['pid' => $form->project_id, 'fid' => $form->id, 'rid' => $record->id, 'flid' => $flid]) }}" class="field-btn external-button-js tooltip" tooltip="Open in New Tab">
                    <i class="icon icon-external-link"></i>
                </a>
            </div>

            <div class="bottom">
                <div class="field-btn full-screen-button-js tooltip" tooltip="View Fullscreen">
                    <i class="icon icon-maximize"></i>
                </div>
            </div>
        </div>
        <div id="map{{$flid}}_{{$record->kid}}" class="geolocator-map geolocator-map-js mt-xxs" map-id="{{$flid}}_{{$record->kid}}">

          @foreach($typedField->processDisplayData($field, $value) as $loc)
              <span class="geolocator-location-js hidden" loc-desc="{{$loc['description']}}"
                    loc-x="{{$loc['geometry']['location']['lat']}}"
                    loc-y="{{$loc['geometry']['location']['lng']}}"></span>
          @endforeach

          <div class="full-screen-modal modal modal-js modal-mask geolocator-map-modal geolocator-map-modal-js">
              <div class="content">
                  <div class="body">
                      <a href="#" class="modal-toggle modal-toggle-js">
                          <i class="icon icon-cancel"></i>
                      </a>

                          <div id="modalmap{{$flid}}_{{$record->kid}}" class="geolocator-modal-map geolocator-modal-map-js"></div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
@endif
