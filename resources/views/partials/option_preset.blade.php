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
            alert("This field has no options that can be saved as a preset");
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
                alert("Sorry, the preset could not be saved.  Make sure you entered a name and value for the preset.");
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
                alert("Sorry, the preset could not be applied");
                location.reload();
            }
        });

    }
</script>

    <hr>

    <div class="select_preset">
        <div class="form-group">
             <label for="existing_presets">Presets:</label>
             <select name="existing_presets" id="existing_presets" class="form-control">
               @foreach($presets as $type=>$subset)
                   @foreach($subset as $key=>$supported_preset)
                         <option value="{{$supported_preset->id}}">{{$supported_preset->name}}

                            @if(($type == "Shared"))
                               | shared from {{$supported_preset->project()->first()->name}}
                            @elseif($type == "Stock")
                               | Stock
                            @endif
                         </option>
                    @endforeach
               @endforeach
             </select>
        </div>

        <span><button id="preset_apply" onclick="ApplyPreset()" class="btn btn-primary form-control">Apply an Existing Preset</button></span>
    </div>

    <hr>

    <div class="create_preset">

        <div class="form-group">
            <label for="preset_name">Preset Name:</label>
            <input name="preset_name" id="preset_name" type="text" class="form-control">
        </div>
        <div class="form-group">
            <label for="preset_shared">Share with all projects:</label>
            <input id="preset_shared" type="checkbox" name="preset_shared">
        </div>
        <span><button id="preset_submit" onclick="SavePreset()" class="btn btn-primary form-control">Create a New Preset</button></span>
    </div>

</div>



<script>



</script>




</script>