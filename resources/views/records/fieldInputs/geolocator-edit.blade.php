<div class="form-group">
    <?php
    if($geolocator==null){
        $value = '';
        $value2 = \App\Http\Controllers\FieldController::getDateList($field);
    }else{
        $value = explode('[!]',$geolocator->locations);
        $value2 = array();
        foreach($value as $val){
            $value2[$val] = $val;
        }
    }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    <div class="list_option_form{{$field->flid}}">
        <div>
            {!! Form::select($field->flid.'[]',$value2,$value,
            ['class' => 'form-control list-options'.$field->flid, 'Multiple', 'id' => 'list'.$field->flid]) !!}
            <button type="button" class="btn btn-primary remove_option{{$field->flid}}">Delete</button>
            <button type="button" class="btn btn-primary move_option_up{{$field->flid}}">Up</button>
            <button type="button" class="btn btn-primary move_option_down{{$field->flid}}">Down</button>
        </div>
        <div class="latlon_container{{$field->flid}}">
            {!! Form::label($field->flid, 'Description: ') !!}
            <span><input type="text" class="latlon_desc{{$field->flid}}"></span>
            {!! Form::label($field->flid, 'Latitude: ') !!}
            <span><input type="number" class="latlon_lat{{$field->flid}}" min=-90 max=90 step=".000001"></span>
            {!! Form::label($field->flid, 'Longitude: ') !!}
            <span><input type="number" class="latlon_lon{{$field->flid}}" min=-180 max=180 step=".000001"></span>
            <span><button type='button' class="btn btn-primary add_latlon{{$field->flid}}">Add</button></span>
        </div>
        <div class="utm_container{{$field->flid}}" style="display:none">
            {!! Form::label($field->flid, 'Description: ') !!}
            <span><input type="text" class="utm_desc{{$field->flid}}"></span>
            {!! Form::label($field->flid, 'Zone: ') !!}
            <span><input type="text" class="utm_zone{{$field->flid}}"></span>
            {!! Form::label($field->flid, 'Easting: ') !!}
            <span><input type="text" class="utm_east{{$field->flid}}"></span>
            {!! Form::label($field->flid, 'Northing: ') !!}
            <span><input type="text" class="utm_north{{$field->flid}}"></span>
            <span><button type='button' class="btn btn-primary add_utm{{$field->flid}}">Add</button></span>
        </div>
        <div class="text_container{{$field->flid}}" style="display:none">
            {!! Form::label($field->flid, 'Description: ') !!}
            <span><input type="text" class="text_desc{{$field->flid}}"></span>
            {!! Form::label($field->flid, 'Address: ') !!}
            <span><input type="text" class="text_addr{{$field->flid}}"></span>
            <span><button type='button' class="btn btn-primary add_text{{$field->flid}}">Add</button></span>
        </div>
    </div>
</div>

@section('footer')
    <script>
        $('.list_option_form{{$field->flid}}').on('click', '.remove_option{{$field->flid}}', function(){
            $('option:selected', '#list{{$field->flid}}').remove();
        });
        $('.list_option_form{{$field->flid}}').on('click', '.move_option_up{{$field->flid}}', function(){
            $('#list{{$field->flid}}').find('option:selected').each(function() {
                $(this).insertBefore($(this).prev());
            });
        });
        $('.list_option_form{{$field->flid}}').on('click', '.move_option_down{{$field->flid}}', function(){
            $('#list{{$field->flid}}').find('option:selected').each(function() {
                $(this).insertAfter($(this).next());
            });
        });
        $('.latlon_container{{$field->flid}}').on('click', '.add_latlon{{$field->flid}}', function() {
            desc = $('.latlon_desc{{$field->flid}}').val();
            desc = desc.trim();
            lat = $('.latlon_lat{{$field->flid}}').val();
            lat = lat.trim();
            lon = $('.latlon_lon{{$field->flid}}').val();
            lon = lon.trim();

            if(desc!='' && lat!='' && lon!='') {
                $('#list{{$field->flid}}').append($('<option/>', {
                    value: desc + ': ' + lat + ', ' + lon,
                    text: desc + ': ' + lat + ', ' + lon,
                    selected: 'selected'
                }));
                $('.latlon_desc{{$field->flid}}').val('');
                $('.latlon_lat{{$field->flid}}').val('');
                $('.latlon_lon{{$field->flid}}').val('');
            }
        });
        $('.utm_container').on('click', '.add_utm', function() {
            console.log("utm");
        });
        $('.text_container').on('click', '.add_text', function() {
            console.log("text");
        });
    </script>
@stop