<div>

<script>

   //var preset_type = "{{$field->type}}";

   function getPresetValues(preset_type,preset_cl_id){

        valuearray = [];
        if(preset_type == "Multi-Select List" || preset_type == "Generated List" || preset_type == "List"){
            options = new Array();
            console.log("Preset CL ID: "+preset_cl_id);
            if(preset_cl_id == "one") {
                for (var i = 0; i < $(".list_optionsone")[0].length; i++) {
                    options.push($(".list_optionsone")[0][i].value);
                }
            }
            else if(preset_cl_id == "two") {
                for (var i = 0; i < $(".list_optionstwo")[0].length; i++) {
                    options.push($(".list_optionstwo")[0][i].value);
                }
            }
            valuearray[0] = 'List';
            valuearray[1] = options;
            console.log("List Type -> Options" + valuearray[1])
        }
        else if(preset_type == "Text"){
            valuearray[0] = "Text";
            if(preset_cl_id == "one"){
                valuearray[1] = $("#regex_one").val();
                console.log("Text -> One "+valuearray);
            }
            else if(preset_cl_id == "two") {
                valuearray[1] = $("#regex_two").val();
                console.log("Text -> Two "+valuearray);
            }
        }
        else if(preset_type == "Combo List"){
            valuearray[0] = "Combo List";
            if(preset_cl_id == "one"){
                valuearray[1] = $(preset_cl_id).val();
                console.log("Combo List -> One "+valuearray);
            }
        }
        else{
            alert("{{trans('partials_option_preset.nooptions')}}");
        }

        return valuearray;
   }


    function SavePreset(preset_type,preset_cl_id) {

        $(".alert").remove();
        presetValue = getPresetValues(preset_type,preset_cl_id);

        $.ajax({
            url: "{{ action('OptionPresetController@create',['pid' => $field->pid]) }}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                type: presetValue[0], //'List',
                name: $("#preset_name_"+preset_cl_id).val(),
                preset: presetValue[1], //options,
                shared: $("#preset_shared_"+preset_cl_id).prop('checked')
            },
            success: function (result) {
                document.location.href = "#top";
                location.reload();
            },
            error: function(result){
                alert("{{trans('partials_option_preset.sorrylong')}}.");
                $("#preset_submit").removeClass("disabled");
            }
        });
    }

    function ApplyPreset(comboField){

        $.ajax({
            url: "{{ action('OptionPresetController@applyPreset',['pid' => $field->pid,'fid'=>$form->fid,'flid'=>$field->flid]) }}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                id: $("#existing_presets").val(),
                combo_subfield: comboField,
            },
            success: function (result) {
               location.reload();
            },
            error: function(result){
                alert("{{trans('partials_option_preset.sorryshort')}}");
                location.reload();
            }
        });

    }
   function ApplyPreset2(comboField){

       $.ajax({
           url: "{{ action('OptionPresetController@applyPreset',['pid' => $field->pid,'fid'=>$form->fid,'flid'=>$field->flid]) }}",
           type: 'POST',
           data: {
               "_token": "{{ csrf_token() }}",
               id: $("#existing_presets_2").val(),
               combo_subfield: comboField,
           },
           success: function (result) {
               location.reload();
           },
           error: function(result){
               alert("{{trans('partials_option_preset.sorryshort')}}");
               location.reload();
           }
       });

   }
</script>

    <hr>
    @if(\App\Http\Controllers\OptionPresetController::supportsPresets($oneType))
        <h4>{{trans('fields_options_combolist.presets')}} {{ $oneName }}</h4>
        <div class="select_preset">
            <div class="form-group">
                 <label for="existing_presets">{{trans('partials_option_preset.presets')}}:</label>
                 <select name="existing_presets" id="existing_presets" class="form-control">
                   @foreach($presetsOne as $type=>$subset)
                       @foreach($subset as $key=>$supported_preset)
                             <option value="{{$supported_preset->id}}">{{$supported_preset->name}}

                                @if(($type == "Shared"))
                                   | {{trans('partials_option_preset.shared')}} {{$supported_preset->project()->first()->name}}
                                @elseif($type == "Stock")
                                   | {{trans('partials_option_preset.stock')}}
                                @endif
                             </option>
                        @endforeach
                   @endforeach
                 </select>
            </div>

            <span><button id="preset_apply" onclick="ApplyPreset('one')" class="btn btn-primary form-control">{{trans('partials_option_preset.apply')}}</button></span>
        </div>

        <hr>

        <div class="create_preset">

            <div class="form-group">
                <label for="preset_name">{{trans('partials_option_preset.preset')}}:</label>
                <input name="preset_name" id="preset_name_one" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label for="preset_shared">{{trans('partials_option_preset.share')}}:</label>
                <input id="preset_shared_one" type="checkbox" name="preset_shared">
            </div>
            <span><button id="preset_submit" onclick="SavePreset('{{$oneType}}','one')" class="btn btn-primary form-control">{{trans('partials_option_preset.create')}}</button></span>
        </div>
    @endif
    {{--2nd field of Combo List--}}
    @if(\App\Http\Controllers\OptionPresetController::supportsPresets($twoType))
        <h4>{{trans('fields_options_combolist.presets')}} {{ $twoName }}</h4>
        <div class="select_preset">
            <div class="form-group">
                <label for="existing_presets">{{trans('partials_option_preset.presets')}}:</label>
                <select name="existing_presets" id="existing_presets_2" class="form-control">
                    @foreach($presetsTwo as $type=>$subset)
                        @foreach($subset as $key=>$supported_preset)
                            <option value="{{$supported_preset->id}}">{{$supported_preset->name}}

                                @if(($type == "Shared"))
                                    | {{trans('partials_option_preset.shared')}} {{$supported_preset->project()->first()->name}}
                                @elseif($type == "Stock")
                                    | {{trans('partials_option_preset.stock')}}
                                @endif
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <span><button id="preset_apply" onclick="ApplyPreset2('two')" class="btn btn-primary form-control">{{trans('partials_option_preset.apply')}}</button></span>
        </div>

        <hr>

        <div class="create_preset">

            <div class="form-group">
                <label for="preset_name">{{trans('partials_option_preset.preset')}}:</label>
                <input name="preset_name" id="preset_name_two" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label for="preset_shared">{{trans('partials_option_preset.share')}}:</label>
                <input id="preset_shared_two" type="checkbox" name="preset_shared">
            </div>
            <span><button id="preset_submit" onclick="SavePreset('{{$twoType}}','two')" class="btn btn-primary form-control">{{trans('partials_option_preset.create')}}</button></span>
        </div>
    @endif

</div>



<script>



</script>




</script>