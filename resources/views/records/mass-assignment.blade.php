@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <script>
        field_array = [['default','default']];
        function addField(flid,ftype){
            field_array.push([flid,ftype])
        }
    </script>
    <h1>{{trans('records_mass-assignment.mass')}} {{ $form->name }}</h1>

    <hr/>

    <form method="post" action="{{action('RecordController@massAssignRecords',compact('pid','fid'))}}">
        {!! Form::token() !!}
        <div class="form-group">
            <label for="field_selection">{{trans('records_mass-assignment.field')}}:</label>
            <select class="form-control" name="field_selection" id="field_selection">
                <option value="default">-{{trans('records_mass-assignment.select')}}-</option>
                @foreach($fields as $field)
                    <script>
                        addField(parseInt({{$field->flid}}),("{{$field->type}}"));
                    </script>
                    <option value={{$field->flid}}>{{$field->name}}</option>
                @endforeach
            </select>
            <hr/>
            <div id="field_default">
               <p>{{trans('records_mass-assignment.none')}}.</p>
            </div>
        </div>
            @foreach($fields as $field)
                <div id="field_{{$field->flid}}" style="display:none">
                    @if($field->type == "Text")
                        @include('records.fieldInputs.text')
                    @endif
                    @if($field->type == "Rich Text")
                        @include('records.fieldInputs.richtext')
                    @endif
                    @if($field->type == "Number")
                        @include('records.fieldInputs.number')
                    @endif
                    @if($field->type == "List")
                        @include('records.fieldInputs.list')
                    @endif
                    @if($field->type == "Multi-Select List")
                        @include('records.fieldInputs.mslist')
                    @endif
                    @if($field->type == "Generated List")
                        @include('records.fieldInputs.genlist')
                    @endif
                    @if($field->type =="Combo List")
                        @include('records.fieldInputs.combolist')
                    @endif
                    @if($field->type == "Date")
                        @include('records.fieldInputs.date')
                    @endif
                    @if($field->type == "Schedule")
                        @include('records.fieldInputs.schedule')
                            {{-- The script is being placed in the footer, and that can be overriden by others, so this needs to be copied here. --}}
                            <script>
                                $('#startdatetime{{$field->flid}}').datetimepicker({
                                    minDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}',
                                    maxDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}'
                                });
                                $('#enddatetime{{$field->flid}}').datetimepicker({
                                    minDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}',
                                    maxDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}'
                                });

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
                                $('.list_option_form{{$field->flid}}').on('click', '.add_option{{$field->flid}}', function() {
                                    name = $('#eventname{{$field->flid}}').val().trim();
                                    sTime = $('#startdatetime{{$field->flid}}').val().trim();
                                    eTime = $('#enddatetime{{$field->flid}}').val().trim();

                                    $('#eventname{{$field->flid}}').css({ "border": ''});
                                    $('#startdatetime{{$field->flid}}').css({ "border": ''});
                                    $('#enddatetime{{$field->flid}}').css({ "border": ''});

                                    if(name==''|sTime==''|eTime==''){
                                        //show error
                                        if(name=='')
                                            $('#eventname{{$field->flid}}').css({ "border": '#FF0000 1px solid'});
                                        if(sTime=='')
                                            $('#startdatetime{{$field->flid}}').css({ "border": '#FF0000 1px solid'});
                                        if(eTime=='')
                                            $('#enddatetime{{$field->flid}}').css({ "border": '#FF0000 1px solid'});
                                    }else {
                                        if ($('#allday{{$field->flid}}').is(":checked")) {
                                            sTime = sTime.split(" ")[0];
                                            eTime = eTime.split(" ")[0];
                                        }

                                        if(sTime>eTime){
                                            $('#startdatetime{{$field->flid}}').css({ "border": '#FF0000 1px solid'});
                                            $('#enddatetime{{$field->flid}}').css({ "border": '#FF0000 1px solid'});
                                        }else {

                                            val = name + ': ' + sTime + ' - ' + eTime;

                                            if (val != '') {
                                                $('#list{{$field->flid}}').append($("<option/>", {
                                                    value: val,
                                                    text: val,
                                                    selected: 'selected'
                                                }));
                                                $('#eventname{{$field->flid}}').val('');
                                                $('#startdatetime{{$field->flid}}').val('');
                                                $('#enddatetime{{$field->flid}}').val('');
                                            }
                                        }
                                    }
                                });
                            </script>
                    @endif
                    @if($field->type == "Geolocator")
                        @include('records.fieldInputs.geolocator')
                        {{-- The script is being placed in the footer, and that can be overriden by others, so this needs to be copied here. --}}
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
                    @endif
                    @if($field->type == "Associator")
                        @include('records.fieldInputs.associator')
                    @endif
                </div>
            @endforeach

        <script>
            var prev = "default";
            //This handles switching between the fields, shouldn't need to be updated even if you add new fields
            $("#field_selection").on('change',function(){
                for(var i = 0; i<field_array.length; i++) {
                    if (this.value == field_array[i][0]) {
                        $("#field_"+prev).hide('slow');
                        prev = this.value;
                        $(".select2").css("width","100%");
                        $("#field_"+field_array[i][0]).show('slow');
                    }
                }
                //This prevents the default option from being submitted accidentally
                if(this.value == "default"){
                    $("#submit").addClass("disabled");
                }
                else{
                    $("#submit").removeClass("disabled");
                }
            });
            //This is in case the user refreshes the page or uses the back button, when the browser fills the fields
            //it could have 1 field selected but another displaying, this forces them back to the default
            $(document).ready(function(){
                for(var i = 0; i<field_array.length; i++){
                    $("#field_"+field_array[i][0]).hide();
                }
                $('#field_selection').val('default');
                $("#submit").addClass("disabled");
                $("#field_default").show();

            });

        </script>

        <div class="form-group">
            <label for="overwrite">{{trans('records_mass-assignment.overwrite')}}:
                <input name="overwrite" value="True" type="checkbox">
            </label>
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-primary form-control" id="submit" value="{{trans('records_mass-assignment.submit')}}">
        </div>
    </form>

    @include('errors.list')

    @section('footer')

    @endsection
@stop