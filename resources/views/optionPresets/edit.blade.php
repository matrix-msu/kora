@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('content')
    <h1>{{$preset->name}} | {{$preset->type}}</h1>

    <hr/>

    @if($preset->shared == true)
        <div class="form-group">
            <label for="preset_shared">{{trans('optionPresets_edit.share')}}:</label>
            <input id="preset_shared" type="checkbox" name="preset_shared" checked>
        </div>
    @else
        <div class="form-group">
            <label for="preset_shared">{{trans('optionPresets_edit.share')}}:</label>
            <input id="preset_shared" type="checkbox" name="preset_shared">
        </div>
    @endif

    <div class="form-group">
        <label for="preset_name">{{trans('optionPresets_edit.name')}}:</label>
        <input name="preset_name" id="preset_name" class="form-control" type="text" value="{{$preset->name}}">
    </div>
    <div class="form-group">
        <input id="submit_preset_name" type="submit" value="{{trans('optionPresets_edit.updatename')}}" class="btn btn-primary form-control">
    </div>
    @if($preset->type == "Text")
        <div class="form-group">
            <label for="preset_regex">{{trans('optionPresets_edit.regex')}}:</label>
            <input name="preset_regex" id="preset_regex" class="form-control" type="text" value="{{$preset->preset}}">
        </div>
        <div class="form-group">
            <input id="preset_regex_submit" type="submit" value="{{trans('optionPresets_edit.updateregex')}}" class="btn btn-primary form-control">
        </div>
    @elseif($preset->type == "List")
        <div class="list_option_form form-group">

            <select multiple class="form-control list_options">
                @foreach(\App\Http\Controllers\OptionPresetController::getList($preset->id,false) as $opt)
                    <option value="{{$opt}}">{{$opt}}</option>
                @endforeach
            </select>
            <button class="btn btn-primary remove_option">{{trans('optionPresets_edit.delete')}}</button>
            <button class="btn btn-primary move_option_up">{{trans('optionPresets_edit.up')}}</button>
            <button class="btn btn-primary move_option_down">{{trans('optionPresets_edit.down')}}</button>
            <div>
                <span><input type="text" class="new_list_option"></span>
                <span><button class="btn btn-primary add_option">{{trans('optionPresets_edit.add')}}</button></span>
            </div>
        </div>
    @elseif($preset->type == "Schedule")
        <div id="preset_schedule" class="list_option_form form-group sched_events_select">
            <div>
                <label for="preset_schedule_events">{{trans('optionPresets_edit.events')}}:</label>
                <select name="preset_schedule_events" id="preset_schedule_events" multiple class="form-control list_options schedule_events" style="overflow:auto">
                    @foreach(\App\Http\Controllers\OptionPresetController::getList($preset->id,false) as $opt)
                        <option value="{{$opt}}">Description: {{explode('[Desc]',$opt)[1]}} | LatLon: {{explode('[LatLon]',$opt)[1]}} | UTM: {{explode('[UTM]',$opt)[1]}} | Address: {{explode('[Address]',$opt)[1]}}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary remove_option">{{trans('optionPresets_edit.delete')}}</button>
                <button class="btn btn-primary move_option_up">{{trans('optionPresets_edit.up')}}</button>
                <button class="btn btn-primary move_option_down">{{trans('optionPresets_edit.down')}}</button>
            </div>
            <div class="form-inline" style="position:relative">
                {!! Form::label('eventname',trans('optionPresets_edit.title').': ') !!}
                <input type="text" class="form-control" id="eventname" maxlength="24"/>
                {!! Form::label('startdatetime',trans('optionPresets_edit.start').': ') !!}
                <input type='text' class="form-control" id='startdatetime' />
                {!! Form::label('enddatetime',trans('optionPresets_edit.end').': ') !!}
                <input type='text' class="form-control" id='enddatetime' />
                {!! Form::label('allday',trans('optionPresets_edit.allday').': ') !!}
                <input type='checkbox' class="form-control" id='allday' />
                <button class="btn btn-primary add_event">{{trans('optionPresets_edit.add')}}</button>
            </div>
        </div>
    @elseif($preset->type == "Geolocator")
        <div id="preset_geolocator" class="list_option_form">
            <div>
                <label for="preset_geolocator_locations">{{trans('optionPresets_edit.loc')}}:</label>
                <select name="preset_geolocator_locations" id="preset_geolocator_locations" multiple class="form-control list_options geolocator_locations" style="overflow:auto">
                    @foreach(\App\Http\Controllers\OptionPresetController::getList($preset->id,false) as $opt)
                        <option value="{{$opt}}">{{$opt}}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary remove_option">{{trans('optionPresets_edit.delete')}}</button>
                <button class="btn btn-primary move_option_up">{{trans('optionPresets_edit.up')}}</button>
                <button class="btn btn-primary move_option_down">{{trans('optionPresets_edit.down')}}</button>
            </div>

            <div>
                {!! Form::label('type', trans('fields_options_geolocator.type').': ') !!}
                {!! Form::select('loc_type', ['LatLon' => 'LatLon','UTM' => 'UTM','Address' => trans('fields_options_geolocator.addr')], 'LatLon', ['class' => 'form-control loc_type']) !!}
            </div>

            <div class="latlon_container">
                <label>{{trans('optionPresets_edit.loc')}}:</label>
                <input type="text" class="form-control latlon_desc">
                <label>{{trans('optionPresets_edit.lat')}}:</label>
                <input type="number" class="form-control latlon_lat" min=-90 max=90 step=".000001">
                <label>{{trans('optionPresets_edit.lon')}}:</label>
                <input type="number" class="form-control latlon_lon" min=-180 max=180 step=".000001">
                <button class="btn btn-primary add_latlon">{{trans('optionPresets_edit.add')}}</button>
            </div>
            <div class="utm_container" style="display:none">
                <label>{{trans('optionPresets_edit.desc')}}:</label>
                <input type="text" class="form-control utm_desc">
                <label>{{trans('optionPresets_edit.zone')}}:</label>
                <input type="text" class="form-control utm_zone">
                <label>{{trans('optionPresets_edit.east')}}:</label>
                <input type="text" class="form-control utm_east">
                <label>{{trans('optionPresets_edit.north')}}:</label>
                <input type="text" class="form-control utm_north">
                <button class="btn btn-primary add_utm">{{trans('optionPresets_edit.add')}}</button>
            </div>
            <div class="text_container" style="display:none">
                <label>{{trans('optionPresets_edit.desc')}}:</label>
                <input type="text" class="form-control text_desc">
                <label>{{trans('optionPresets_edit.addr')}}:</label>
                <input type="text" class="form-control text_addr">
                <button class="btn btn-primary add_text">{{trans('optionPresets_edit.add')}}</button>
            </div>
        </div>
    @endif


@stop

@section('footer')
    <script>

        $("#preset_shared").on('click',function(){
            $.ajax({
                url: '{{ action('OptionPresetController@update',['pid' => $pid, 'id'=>$id]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    action: 'changeSharing',
                     preset_shared: JSON.parse($("#preset_shared").prop('checked'))
                },
                success: function (result) {
                    console.log(result);
                },
                error: function(result){
                    console.log(result);
                    location.reload();
                }
            });
        });


        //List fields, Schedule, and Geolocator
            $('#default').select2();

            $('.list_option_form').on('click', '.remove_option', function(){
                val = $('option:selected', '.list_options').val();

                $('option:selected', '.list_options').remove();
                $("#default option[value='"+val+"']").remove();
                SaveList();
            });
            $('.list_option_form').on('click', '.move_option_up', function(){
                val = $('option:selected', '.list_options').val();
                defOpt = $("#default option[value='"+val+"']");

                $('.list_options').find('option:selected').each(function() {
                    $(this).insertBefore($(this).prev());
                });
                defOpt.insertBefore(defOpt.prev());
                SaveList();
            });

            $('.list_option_form').on('click', '.move_option_down', function(){
                val = $('option:selected', '.list_options').val();
                defOpt = $("#default option[value='"+val+"']");

                $('.list_options').find('option:selected').each(function() {
                    $(this).insertAfter($(this).next());
                });
                defOpt.insertAfter(defOpt.next());
                SaveList();
            });

            $('.list_option_form').on('click', '.add_option', function(){
                val = $('.new_list_option').val();
                val = val.trim();

                if(val != '') {
                    $('.list_options').append($("<option/>", {
                        value: val,
                        text: val
                    }));
                    $('#default').append($("<option/>", {
                        value: val,
                        text: val
                    }));
                    $('.new_list_option').val('');
                    SaveList();
                }
            });

            function SaveList() {
                options = [];
                $(".list_options option").each(function(){
                    options.push($(this).val());
                });

                $.ajax({
                    url: '{{ action("OptionPresetController@saveList",['pid'=>$pid,'id'=>$id])}}',
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        action: 'SaveList',
                        options: options
                    },
                    error: function(result){
                        location.reload();
                    }
                });
            }
        //End

        //Specific to schedule

        $('#startdatetime').datetimepicker();
        $('#enddatetime').datetimepicker();

        $('.sched_events_select').on('click', '.add_event', function() {
            name = $('#eventname').val().trim();
            sTime = $('#startdatetime').val().trim();
            eTime = $('#enddatetime').val().trim();

            $('#eventname').css({ "border": ''});
            $('#startdatetime').css({ "border": ''});
            $('#enddatetime').css({ "border": ''});

            if(name==''|sTime==''|eTime==''){
                //show error
                if(name=='')
                    $('#eventname').css({ "border": '#FF0000 1px solid'});
                if(sTime=='')
                    $('#startdatetime').css({ "border": '#FF0000 1px solid'});
                if(eTime=='')
                    $('#enddatetime').css({ "border": '#FF0000 1px solid'});
            }else{
                if($('#allday').is(":checked")){
                    sTime = sTime.split(" ")[0];
                    eTime = eTime.split(" ")[0];
                }

                if(sTime>eTime){
                    $('#startdatetime').css({ "border": '#FF0000 1px solid'});
                    $('#enddatetime').css({ "border": '#FF0000 1px solid'});
                }else {

                    val = name + ': ' + sTime + ' - ' + eTime;

                    if (val != '') {
                        $('.list_options').append($("<option/>", {
                            value: val,
                            text: val
                        }));
                        $('#eventname').val('');
                        $('#startdatetime').val('');
                        $('#enddatetime').val('');
                        SaveList();
                    }
                }
            }
        });

        //End

        //Specific to Geolocator

        /*********
         * Adds new locations for geolocator field
         * This is slightly modified so that it conflicts less when on the same page
         * as types like lists and schedule
         **********/
        $('.latlon_container').on('click', '.add_latlon', function() {
            desc = $('.latlon_desc').val();
            desc = desc.trim();
            lat = $('.latlon_lat').val();
            lat = lat.trim();
            lon = $('.latlon_lon').val();
            lon = lon.trim();

            if(desc!='' && lat!='' && lon!='') {

                // Properly format the geolocator information.
                $.ajax({
                    url: '{{ action('FieldAjaxController@geoConvert',['pid' => 0, 'fid' => 0, 'flid' => 0]) }}',
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        lat: lat,
                        lon: lon,
                        type: 'latlon'
                    },
                    success:function(result) {
                        result = '[Desc]'+desc+'[Desc]'+result;
                        $('.list_options').append($("<option/>", {
                            value: result,
                            text: result
                        }));

                        SaveList();
                    }
                });
                $('.latlon_desc').val('');
                $('.latlon_lat').val('');
                $('.latlon_lon').val('');
            }
            else {
                $('.latlon_desc').attr('style','border: 1px solid red;');
                $('.latlon_lat').attr('style','border: 1px solid red;');
                $('.latlon_lon').attr('style','border: 1px solid red;');
            }
        });

        $('.utm_container').on('click', '.add_utm', function() {
            desc = $(".utm_desc").val().trim();
            zone = $(".utm_zone").val().trim();
            east = $(".utm_east").val().trim();
            north  = $(".utm_north").val().trim();

            if (desc != "" && zone != "" && east != "" && north != "") {
                // Properly format geolocator information.
                $.ajax({
                    url: '{{ action('FieldAjaxController@geoConvert',['pid' => 0, 'fid' => 0, 'flid' => 0]) }}',
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        zone: zone,
                        east: east,
                        north: north,
                        type: 'utm'
                    },
                    success: function (result) {
                        result = '[Desc]'+desc+'[Desc]'+result;
                        $('.list_options').append($("<option/>", {
                            value: result,
                            text: result
                        }));

                        SaveList();
                    }
                });

                $(".utm_desc").val("");
                $(".utm_zone").val("");
                $(".utm_east").val("");
                $(".utm_north").val("");
            }
            else {
                $(".utm_desc").attr('style','border: 1px solid red;');
                $(".utm_zone").attr('style','border: 1px solid red;');
                $(".utm_east").attr('style','border: 1px solid red;');
                $(".utm_north").attr('style','border: 1px solid red;');
            }

        });

        $('.text_container').on('click', '.add_text', function() {
            desc = $(".text_desc").val().trim();
            addr = $(".text_addr").val().trim();

            if (desc != "" && addr != "") {
                // Properly format geolocator information.
                $.ajax({
                    url: '{{ action('FieldAjaxController@geoConvert',['pid' => 0, 'fid' => 0, 'flid' => 0]) }}',
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        addr: addr,
                        type: 'geo'
                    },
                    success: function (result) {
                        result = '[Desc]'+desc+'[Desc]'+result;
                        $('.list_options').append($("<option/>", {
                            value: result,
                            text: result
                        }));

                        SaveList();
                    }
                });

                $(".text_desc").val("");
                $(".text_addr").val("");
            }
            else {
                $(".text_desc").attr('style','border: 1px solid red;');
                $(".text_addr").attr('style','border: 1px solid red;');
            }
        });

        /**
         * Changes the geolocator form inputs.
         */
        $('.list_option_form').on('change', '.loc_type', function(){
            newType = $('.loc_type').val();
            if(newType=='LatLon'){
                $('.latlon_container').show();
                $('.utm_container').hide();
                $('.text_container').hide();
            }else if(newType=='UTM'){
                $('.latlon_container').hide();
                $('.utm_container').show();
                $('.text_container').hide();
            }else if(newType=='Address'){
                $('.latlon_container').hide();
                $('.utm_container').hide();
                $('.text_container').show();
            }
        });

        //End

        //For editing presets

        $("#submit_preset_name").on('click',SavePresetName);

        $("#preset_regex_submit").on('click',SavePresetRegex);

        function SavePresetName() {
            $.ajax({
                url: '{{ action('OptionPresetController@update',['pid' => $pid, 'id'=>$id]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    action: 'changeName',
                    preset_name: $("#preset_name").val(),
                },
                success: function (result) {
                    console.log(result);
                    location.reload();
                },
                error: function(result){
                    console.log(result);
                    location.reload();
                }
            });
        }

        function SavePresetRegex() {
            $.ajax({
                url: '{{ action('OptionPresetController@update',['pid' => $pid, 'id'=>$id]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    action: 'changeRegex',
                    preset_regex: $("#preset_regex").val(),
                },
                success: function (result) {
                    console.log(result);
                    location.reload();
                },
                error: function(result){
                    console.log(result);
                    location.reload();
                }
            });
        }

        function deletePreset(presetId) {
            var encode = $('<div/>').html(" {{ trans('optionPresets_edit.areyousure') }}").text();
            var response = confirm(encode + "?");
            if (response) {
                $.ajax({
                    url: '{{ action('OptionPresetController@delete')}}',
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "presetId": presetId
                    },
                    success: function (result) {
                        location.reload();
                    },
                    error: function(result){
                        location.reload();
                    }
                });
            }
        }
    </script>
@stop