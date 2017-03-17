<div class="form-group">
    <?php
    if($combolist==null){
        $valArray = array();
    }else{
        $valArray = explode('[!val!]',$combolist->options);
    }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif

    <?php
    $oneType = \App\ComboListField::getComboFieldType($field,'one');
    $twoType = \App\ComboListField::getComboFieldType($field,'two');
    ?>

    <div id="combo_list_{{$field->flid}}" style="overflow: auto">
        {!! Form::hidden($field->flid,true) !!}
        <div>
            <span style="float:left;width:40%;margin-bottom:10px"><b>{{\App\ComboListField::getComboFieldName($field,'one')}}</b></span>
            <span style="float:left;width:40%;margin-bottom:10px"><b>{{\App\ComboListField::getComboFieldName($field,'two')}}</b></span>
            <span style="float:left;width:20%;margin-bottom:10px"><b>{{trans('records_fieldInput.remove')}}</b></span>
        </div>

        @for($i=0;$i<sizeof($valArray);$i++)
            <div id="val_{{$field->flid}}">
                @if($oneType=='Text' | $oneType=='List')
                    <?php $value1 = explode('[!f1!]',$valArray[$i])[1]; ?>
                    <span style="float:left;width:40%;margin-bottom:10px">{{$value1}}</span>
                @elseif($oneType=='Number')
                    <?php
                    $value1 = explode('[!f1!]',$valArray[$i])[1];
                    $unit = \App\ComboListField::getComboFieldOption($field,'Unit','one');
                    if($unit!=null && $unit!=''){
                        $value1 .= ' '.$unit;
                    }
                    ?>
                    <span style="float:left;width:40%;margin-bottom:10px">{{$value1}}</span>
                @elseif($oneType=='Multi-Select List' | $oneType=='Generated List')
                    <?php
                    $value1 = explode('[!f1!]',$valArray[$i])[1];
                    $value1Array = explode('[!]',$value1);
                    ?>

                    <span style="float:left;width:40%;margin-bottom:10px">
                        @foreach($value1Array as $val)
                            <div>{{$val}}</div>
                        @endforeach
                    </span>
                @endif


                @if($twoType=='Text' | $twoType=='List')
                    <?php $value2 = explode('[!f2!]',$valArray[$i])[1]; ?>
                    <span style="float:left;width:40%;margin-bottom:10px">{{$value2}}</span>
                @elseif($twoType=='Number')
                    <?php
                    $value2 = explode('[!f2!]',$valArray[$i])[1];
                    $unit = \App\ComboListField::getComboFieldOption($field,'Unit','two');
                    if($unit!=null && $unit!=''){
                        $value2 .= ' '.$unit;
                    }
                    ?>
                    <span style="float:left;width:40%;margin-bottom:10px">{{$value2}}</span>
                @elseif($twoType=='Multi-Select List' | $twoType=='Generated List')
                    <?php
                    $value2 = explode('[!f2!]',$valArray[$i])[1];
                    $value2Array = explode('[!]',$value2);
                    ?>

                    <span style="float:left;width:40%;margin-bottom:10px">
                        @foreach($value2Array as $val)
                            <div>{{$val}}</div>
                        @endforeach
                    </span>
                @endif

                {!! Form::hidden($field->flid.'_val[]',"[!f1!]".$value1."[!f1!][!f2!]".$value2."[!f2!]") !!}

                <span class="delete_combo_def_{{$field->flid}}" style="float:left;width:20%;margin-bottom:10px"><a>[X]</a></span>
            </div>
        @endfor
    </div>

    <div class="form-group" id="combo_inputs_{{$field->flid}}">
        <div style="color: red" id="combo_error_{{$field->flid}}"></div>
        @include('partials.combofields.newrec_inputs',['field'=>$field, 'type'=>$oneType, 'fnum'=>'one', 'flid'=>$field->flid])
        @include('partials.combofields.newrec_inputs',['field'=>$field, 'type'=>$twoType, 'fnum'=>'two', 'flid'=>$field->flid])
        <button type="button" id="combo_add_val_{{$field->flid}}" class="btn btn-default form-control">{{trans('records_fieldInput.addval')}}</button>
    </div>
</div>

<script>
    $('#combo_inputs_{{$field->flid}}').on('click', '#combo_add_val_{{$field->flid}}', function() {
        $("#combo_error_{{$field->flid}}").text('');

        val1Div = $(this).siblings('#default_one_{{$field->flid}}');
        val2Div = $(this).siblings('#default_two_{{$field->flid}}');
        span1 = '';
        span2 = '';

        val1 = val1Div.val();
        type1 = '{{$oneType}}';
        val2 = val2Div.val();
        type2 = '{{$twoType}}';
        console.log(val1Div);
        console.log(val2Div);

        //if we have number, we extract differently
        if(type1=='Number'){
            val1 = val1Div.val();
        }
        if(type2=='Number'){
            val2 = val2Div.val();
        }

        $.ajax({
            url: '{{ action('FieldAjaxController@validateComboListOpt',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                valone: val1,
                valtwo: val2,
                typeone: type1,
                typetwo: type2
            },
            success: function (result) {console.log('returned');
                if(result=='' | result==null){
                    if(Array.isArray(val1)){
                        aval1 = val1;
                        val1 = aval1[0];
                        for(var i=1; i<aval1.length; i++){
                            val1 += '[!]'+aval1[i];
                        }
                        span1 = '<span style="float:left;width:40%;margin-bottom:10px">';
                        for(var i=0; i<aval1.length; i++) {
                            span1 += '<div>'+aval1[i]+'</div>';
                        }
                        span1 += '</span>';
                    }else{
                        span1 = '<span style="float:left;width:40%;margin-bottom:10px">'+val1+'</span>';
                    }

                    if(Array.isArray(val2)){
                        aval2 = val2;
                        val2 = aval2[0];
                        for(var i=1; i<aval2.length; i++){
                            val2 += '[!]'+aval2[i];
                        }
                        span2 = '<span style="float:left;width:40%;margin-bottom:10px">';
                        for(var i=0; i<aval2.length; i++) {
                            span2 += '<div>'+aval2[i]+'</div>';
                        }
                        span2 += '</span>';
                    }else{
                        span2 = '<span style="float:left;width:40%;margin-bottom:10px">'+val2+'</span>';
                    }

                    val = '[!f1!]'+val1+'[!f1!][!f2!]'+val2+'[!f2!]';

                    //build the html
                    var html = '<div id="val_{{$field->flid}}">'+span1+span2;
                    html += '<input name="{{$field->flid}}_val[]" type="hidden" value="'+val+'" id="{{$field->flid}}_val[]">';
                    html += '<span class="delete_combo_def_{{$field->flid}}" style="float:left;width:20%;margin-bottom:10px"><a>[X]</a></span>';
                    html +='</div>';

                    val1Div.val('');
                    val2Div.val('');

                    $('#combo_list_{{$field->flid}}').append(html);

                }else{
                    $("#combo_error_{{$field->flid}}").text(result);
                }
            }
        });

    });

    $('#combo_list_{{$field->flid}}').on('click', '.delete_combo_def_{{$field->flid}}', function() {
        parentDiv = $(this).parent();

        parentDiv.remove();
    });
</script>