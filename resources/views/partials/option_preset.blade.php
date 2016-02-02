<div>

<script>

   var preset_type = "{{$field->type}}";

   function getPresetValues(){

        valuearray = [];
        if(preset_type == "Multi-Select List" || preset_type == "Generated List" || preset_type == "List"){
            options = new Array();
            $(".list_options option").each(function(){
                options.push($(this).val());
            });
            valuearray[0] = 'List';
            valuearray[1] = options;
        }
        else if(preset_type == "Text"){
            valuearray[0] = "Text";
            valuearray[1] = $("#value").val();
        }
        else if(preset_type == "Geolocator"){
            options = new Array();
            $(".list_options option").each(function(){
                options.push($(this).val());
            });
            valuearray[0] = 'Geolocator';
            valuearray[1] = options;
        }
        else if(preset_type == "Schedule"){
            options = new Array();
            $(".list_options option").each(function(){
                options.push($(this).val());
            });
            valuearray[0] = "Schedule";
            valuearray[1] = options;
        }
        else{
            alert("{{trans('partials_option_preset.nooptions')}}");
        }

        return valuearray;
   }


    function SavePreset() {

        $(".alert").remove();
        presetValue = getPresetValues();

        $.ajax({
            url: "{{ action('OptionPresetController@create',['pid' => $field->pid]) }}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                type: presetValue[0], //'List',
                name: $("#preset_name").val(),
                preset: presetValue[1], //options,
                shared: $("#preset_shared").prop('checked')
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

    function ApplyPreset(){

        $.ajax({
            url: "{{ action('OptionPresetController@applyPreset',['pid' => $field->pid,'fid'=>$form->fid,'flid'=>$field->flid]) }}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                id: $("#existing_presets").val(),
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

    <div class="select_preset">
        <div class="form-group">
             <label for="existing_presets">{{trans('partials_option_preset.presets')}}:</label>
             <select name="existing_presets" id="existing_presets" class="form-control">
               @foreach($presets as $type=>$subset)
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

        <span><button id="preset_apply" onclick="ApplyPreset()" class="btn btn-primary form-control">{{trans('partials_option_preset.apply')}}</button></span>
    </div>

    <hr>

    <div class="create_preset">

        <div class="form-group">
            <label for="preset_name">{{trans('partials_option_preset.preset')}}:</label>
            <input name="preset_name" id="preset_name" type="text" class="form-control">
        </div>
        <div class="form-group">
            <label for="preset_shared">{{trans('partials_option_preset.share')}}:</label>
            <input id="preset_shared" type="checkbox" name="preset_shared">
        </div>
        <span><button id="preset_submit" onclick="SavePreset()" class="btn btn-primary form-control">{{trans('partials_option_preset.create')}}</button></span>
    </div>

</div>



<script>



</script>




</script>