@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('content')
    <h1>{{trans('optionPresets_create.create')}}</h1>
    <hr/>


    <div class="form-group">
        <label for="preset_type">{{trans('optionPresets_create.type')}}</label>
        <select name="preset_type" id="preset_type" class="form-control">
            <option value="default">{{trans('optionPresets_create.preset')}}</option>
            <option value="text">{{trans('optionPresets_create.textregex')}}</option>
            <option value="list">{{trans('optionPresets_create.list')}}</option>
            <option value="schedule">{{trans('optionPresets_create.schedule')}}</option>
            <option value="geolocator">{{trans('optionPresets_create.geo')}}</option>
        </select>
    </div>

    <div class="form-group">
        <label for="preset_name">{{trans('optionPresets_create.name')}}:</label>
        <input name="preset_name" id="preset_name" class="form-control" type="text" value="Name">
    </div>

    <div id="preset_default">

    </div>
    <div style="display:none" id="preset_text" class="form-group">
        <label for="preset_regex">{{trans('optionPresets_create.regex')}}:</label>
        <input name="preset_regex" id="preset_text_regex" class="form-control" type="text" value="">
    </div>

    <div style="display:none" id="preset_list" class="list_option_form form-group">
        <label for="preset_list_options">{{trans('optionPresets_create.options')}}:</label>
        <select name="preset_list_options" id="preset_list_options" multiple class="form-control list_options listtype_options">
        </select>
        <button class="btn btn-primary remove_option">{{trans('optionPresets_create.delete')}}</button>
        <button class="btn btn-primary move_option_up">{{trans('optionPresets_create.up')}}</button>
        <button class="btn btn-primary move_option_down">{{trans('optionPresets_create.down')}}</button>
        <div>
            <span><input type="text" class="new_list_option"></span>
            <span><button class="btn btn-primary add_option">{{trans('optionPresets_create.add')}}</button></span>
        </div>
    </div>

    <div style="display:none" id="preset_schedule" class="list_option_form form-group sched_events_select">
        <div>
            <label for="preset_schedule_events">{{trans('optionPresets_create.events')}}:</label>
            <select name="preset_schedule_events" id="preset_schedule_events" multiple class="form-control list_options schedule_events" style="overflow:auto">
            </select>
            <button class="btn btn-primary remove_option">{{trans('optionPresets_create.delete')}}</button>
            <button class="btn btn-primary move_option_up">{{trans('optionPresets_create.up')}}</button>
            <button class="btn btn-primary move_option_down">{{trans('optionPresets_create.down')}}</button>
        </div>
        <div class="form-inline" style="position:relative">
            {!! Form::label('eventname',trans('optionPresets_create.title').': ') !!}
            <input type="text" class="form-control" id="eventname" maxlength="24"/>
            {!! Form::label('startdatetime',trans('optionPresets_create.start').': ') !!}
            <input type='text' class="form-control" id='startdatetime' />
            {!! Form::label('enddatetime',trans('optionPresets_create.end').': ') !!}
            <input type='text' class="form-control" id='enddatetime' />
            {!! Form::label('allday',trans('optionPresets_create.allday').': ') !!}
            <input type='checkbox' class="form-control" id='allday' />
            <button class="btn btn-primary add_option">{{trans('optionPresets_create.add')}}</button>
        </div>
    </div>

    <div style="display:none" id="preset_geolocator" class="list_option_form">
        <div>
            <label for="preset_geolocator_locations">{{trans('optionPresets_create.loc')}}:</label>
            <select name="preset_geolocator_locations" id="preset_geolocator_locations" multiple class="form-control list_options geolocator_locations" style="overflow:auto">
            </select>
            <button class="btn btn-primary remove_option">{{trans('optionPresets_create.delete')}}</button>
            <button class="btn btn-primary move_option_up">{{trans('optionPresets_create.up')}}</button>
            <button class="btn btn-primary move_option_down">{{trans('optionPresets_create.down')}}</button>
        </div>
        <div class="latlon_container">
            <label>{{trans('optionPresets_create.loc')}}:</label>
            <span><input type="text" class="latlon_desc"></span>
            <label>{{trans('optionPresets_create.lat')}}:</label>
            <span><input type="number" class="latlon_lat" min=-90 max=90 step=".000001"></span>
            <label>{{trans('optionPresets_create.lon')}}:</label>
            <span><input type="number" class="latlon_lon" min=-180 max=180 step=".000001"></span>
            <span><button class="btn btn-primary add_latlon">{{trans('optionPresets_create.add')}}</button></span>
        </div>
        <div class="utm_container" style="display:none">
            <label>{{trans('optionPresets_create.desc')}}:</label>
            <span><input type="text" class="utm_desc"></span>
            <label>{{trans('optionPresets_create.zone')}}:</label>
            <span><input type="text" class="utm_zone"></span>
            <label>{{trans('optionPresets_create.east')}}:</label>
            <span><input type="text" class="utm_east"></span>
            <label>{{trans('optionPresets_create.north')}}:</label>
            <span><input type="text" class="utm_north"></span>
            <span><button class="btn btn-primary add_utm">{{trans('optionPresets_create.add')}}</button></span>
        </div>
        <div class="text_container" style="display:none">
            <label>{{trans('optionPresets_create.desc')}}:</label>
            <span><input type="text" class="text_desc"></span>
            <label>{{trans('optionPresets_create.addr')}}:</label>
            <span><input type="text" class="text_addr"></span>
            <span><button class="btn btn-primary add_text">{{trans('optionPresets_create.add')}}</button></span>
        </div>
    </div>

        <div class="form-group">
            <label for="preset_shared">{{trans('optionPresets_create.share')}}:</label>
            <input id="preset_shared" type="checkbox" name="preset_shared">
        </div>

    <div class="form-group">
        <input onclick="CreatePreset()" type="submit" class="btn btn-primary form-control" id="submit" value="{{trans('optionPresets_create.save')}}">
    </div>



@stop

@section('footer')
    <script>


        /**************
         * Moves list options/events/locations up and down and deletes them
         * There are some problems if multiple types are visible and selected at once
         ********/

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

        //Specific to schedule

        $('#startdatetime').datetimepicker();
        $('#enddatetime').datetimepicker();
        /**************
         * Adds new events to the schedule field
         * This is slightly modified so that it conflicts less when on the same page
         * as types like list and geolocator.
         ********/
        $('.sched_events_select').on('click', '.add_option', function() {
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

        //Delete this
        function SaveList() {
            console.log("SaveList is not saving to the new preset yet");
        }

        //Specific to geolocator

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

        /**************
         This part handles switching the displayed div based on the value of the selected DIv
         field_array needs to be updated when
         ********/
       // var old_field_array = [['default','default'],['text','text'],['list','list'],['schedule','schedule']]
        var field_array = ['default','text','list','schedule','geolocator'];
        var prev = "default";
        $("#preset_type").on('change',function(){
            console.log("switching");
            for(var i = 0; i<field_array.length; i++) {
                if (this.value == field_array[i]) {
                    console.log(prev + " to " +field_array[i]);
                    $("#preset_"+prev).hide('slow');
                    prev = this.value;
                    $("#preset_"+field_array[i]).show('slow');
                }
            }

            //This clears values that conflict between list fields
            $(".list_options option").each(function(){
                $(this).remove();
            });

            //This prevents the default option from being submitted accidentally
            if(this.value == "default"){
                $("#submit").addClass("disabled");
            }
            else{
                $("#submit").removeClass("disabled");
            }
        });

        /**************
         This is in case the user refreshes the page or uses the back button, when the browser fills the fields
         it could have 1 type selected but another displaying, this forces them back to the default
         ********/
        $(document).ready(function(){
            for(var i = 0; i<field_array.length; i++){
                $("#preset_"+field_array[i]).hide();
            }
            $('#preset_type').val('default');
            $("#submit").addClass("disabled");
            $("#preset_default").show();
        });

        /**************
            CreatePreset needs to be updated for every new type added
            OptionPresetController's create method uses the same 4 parameters for every type
            So this reads all the values from the selected type and submits them via AJAX
        ********/
        function CreatePreset() {

            var preset_value = null;
            var preset_type = null;
            if($("#preset_type").val() == "text"){
                preset_type = "Text";
                preset_value = $("#preset_text_regex").val();
            }
            else if($("#preset_type").val() == "list"){
                preset_type = "List";
                options = new Array();
                $(".listtype_options option").each(function(){
                    options.push($(this).val());
                });
                preset_value = options;
            }
            else if($("#preset_type").val() == "schedule"){
                preset_type = "Schedule";
                options = new Array();
                $(".schedule_events option").each(function(){
                    options.push($(this).val());
                });
                preset_value = options;
            }
            else if($("#preset_type").val() == "geolocator"){
                preset_type = "Geolocator";
                options = new Array();
                $(".geolocator_locations option").each(function(){
                    options.push($(this).val());
                });
                preset_value = options;
            }
            $.ajax({
                url: '{{ action('OptionPresetController@create',['pid' => $pid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    type: preset_type,
                    name: $("#preset_name").val(),
                    preset: preset_value,
                    shared: $("#preset_shared").prop('checked')
                },
                success: function (result) {
                    if(result.status = true){
                        window.location = result.url;
                    }
                },
                error: function(result){
                    alert("{{trans('optionPresets_create.sorry')}}.");
                }
            });
        }


    </script>
@stop