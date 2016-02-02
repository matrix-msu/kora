@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('content')
    <h1>{{$preset->name}} | {{$preset->type}}</h1>

    <hr/>

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
                <select name="preset_schedule_events" id="preset_schedule_events" multiple class="form-control list_options schedule_events">
                    @foreach(\App\Http\Controllers\OptionPresetController::getList($preset->id,false) as $opt)
                        <option value="{{$opt}}">{{$opt}}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary remove_option">{{trans('optionPresets_edit.delete')}}</button>
                <button class="btn btn-primary move_option_up">{{trans('optionPresets_edit.up')}}</button>
                <button class="btn btn-primary move_option_down">{{trans('optionPresets_edit.down')}}</button>
            </div>
            <div class="form-inline" style="position:relative">
                {!! Form::label('eventname',trans('optionPresets_edit.title').': ') !!}
                <input type="text" class="form-control" id="eventname" />
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
                <select name="preset_geolocator_locations" id="preset_geolocator_locations" multiple class="form-control list_options geolocator_locations">
                    @foreach(\App\Http\Controllers\OptionPresetController::getList($preset->id,false) as $opt)
                        <option value="{{$opt}}">{{$opt}}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary remove_option">{{trans('optionPresets_edit.delete')}}</button>
                <button class="btn btn-primary move_option_up">{{trans('optionPresets_edit.up')}}</button>
                <button class="btn btn-primary move_option_down">{{trans('optionPresets_edit.down')}}</button>
            </div>
            <div class="latlon_container">
                <label>{{trans('optionPresets_edit.loc')}}:</label>
                <span><input type="text" class="latlon_desc"></span>
                <label>{{trans('optionPresets_edit.lat')}}:</label>
                <span><input type="number" class="latlon_lat" min=-90 max=90 step=".000001"></span>
                <label>{{trans('optionPresets_edit.lon')}}:</label>
                <span><input type="number" class="latlon_lon" min=-180 max=180 step=".000001"></span>
                <span><button class="btn btn-primary add_latlon">{{trans('optionPresets_edit.add')}}</button></span>
            </div>
            <div class="utm_container" style="display:none">
                <label>{{trans('optionPresets_edit.desc')}}:</label>
                <span><input type="text" class="utm_desc"></span>
                <label>{{trans('optionPresets_edit.zone')}}:</label>
                <span><input type="text" class="utm_zone"></span>
                <label>{{trans('optionPresets_edit.east')}}:</label>
                <span><input type="text" class="utm_east"></span>
                <label>{{trans('optionPresets_edit.north')}}:</label>
                <span><input type="text" class="utm_north"></span>
                <span><button class="btn btn-primary add_utm">{{trans('optionPresets_edit.add')}}</button></span>
            </div>
            <div class="text_container" style="display:none">
                <label>{{trans('optionPresets_edit.desc')}}:</label>
                <span><input type="text" class="text_desc"></span>
                <label>{{trans('optionPresets_edit.addr')}}:</label>
                <span><input type="text" class="text_addr"></span>
                <span><button class="btn btn-primary add_text">{{trans('optionPresets_edit.add')}}</button></span>
            </div>
        </div>
    @endif

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
                options = new Array();
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
                    success: function (result) {
                        //location.reload();
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
                $('.list_options').append($('<option/>', {
                    value: desc + ': ' + lat + ', ' + lon,
                    text: desc + ': ' + lat + ', ' + lon
                }));
                SaveList();
                $('.latlon_desc').val('');
                $('.latlon_lat').val('');
                $('.latlon_lon').val('');
            }
        });
        $('.utm_container').on('click', '.add_utm', function() {
            console.log("utm");
        });
        $('.text_container').on('click', '.add_text', function() {
            console.log("text");
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
            var response = confirm("{{trans('optionPresets_edit.areyousure')}}?");
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