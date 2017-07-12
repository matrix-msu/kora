@extends('fields.show')

@section('fieldOptions')
    <?php
            $oneType = \App\ComboListField::getComboFieldType($field,'one');
            $twoType = \App\ComboListField::getComboFieldType($field,'two');
            $oneName = \App\ComboListField::getComboFieldName($field,'one');
            $twoName = \App\ComboListField::getComboFieldName($field,'two');

            $defs = $field->default;
            $defArray = explode('[!def!]',$defs);
    ?>

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldAjaxController@updateOptions', $field->pid, $field->fid, $field->flid], 'onsubmit' => 'selectAll()', 'id' => 'comboform']) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('typeone',$oneType) !!}
    {!! Form::hidden('typetwo',$twoType) !!}
    {!! Form::hidden('nameone',$oneName) !!}
    {!! Form::hidden('nametwo',$twoName) !!}
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_combolist.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('searchable',trans('fields_options_combolist.search').': ') !!}
        {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extsearch',trans('fields_options_combolist.extsearch').': ') !!}
        {!! Form::select('extsearch',['false', 'true'], $field->extsearch, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewable',trans('fields_options_combolist.viewable').': ') !!}
        {!! Form::select('viewable',['false', 'true'], $field->viewable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewresults',trans('fields_options_combolist.viewresults').': ') !!}
        {!! Form::select('viewresults',['false', 'true'], $field->viewresults, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extview',trans('fields_options_combolist.extview').': ') !!}
        {!! Form::select('extview',['false', 'true'], $field->extview, ['class' => 'form-control']) !!}
    </div>

    <hr>

    <div class="form-group">
        {!! Form::label('nameone',trans('fields_options_combolist.nameone').': ') !!}
        {!! Form::text('nameone',$oneName, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('nametwo',trans('fields_options_combolist.nametwo').': ') !!}
        {!! Form::text('nametwo',$twoName, ['class' => 'form-control']) !!}
    </div>

    <div id="combo_defaults" style="overflow: auto">
        {!! Form::label('default', trans('fields_options_combolist.default').': ') !!}
        <div>
            <span style="float:left;width:40%;margin-bottom:10px"><b>{{$oneName}}</b></span>
            <span style="float:left;width:40%;margin-bottom:10px"><b>{{$twoName}}</b></span>
            <span style="float:left;width:20%;margin-bottom:10px"><b>{{trans('fields_options_combolist.remove')}}</b></span>
        </div>
        @if($defs!=null && $defs!='')
            @for($i=0;$i<sizeof($defArray);$i++)
                <div class="default">
                    @if($oneType=='Text' | $oneType=='List')
                        <?php $value = explode('[!f1!]',$defArray[$i])[1]; ?>
                        <span style="float:left;width:40%;margin-bottom:10px">{{$value}}</span>
                    @elseif($oneType=='Number')
                        <?php
                            $value = explode('[!f1!]',$defArray[$i])[1];
                            $unit = \App\ComboListField::getComboFieldOption($field,'Unit','one');
                            if($unit!=null && $unit!=''){
                                $value .= ' '.$unit;
                            }
                        ?>
                        <span style="float:left;width:40%;margin-bottom:10px">{{$value}}</span>
                    @elseif($oneType=='Multi-Select List' | $oneType=='Generated List')
                        <?php
                            $value = explode('[!f1!]',$defArray[$i])[1];
                            $value = explode('[!]',$value);
                        ?>

                        <span style="float:left;width:40%;margin-bottom:10px">
                            @foreach($value as $val)
                                <div>{{$val}}</div>
                            @endforeach
                        </span>
                    @endif


                    @if($twoType=='Text' | $twoType=='List')
                        <?php $value = explode('[!f2!]',$defArray[$i])[1]; ?>
                        <span style="float:left;width:40%;margin-bottom:10px">{{$value}}</span>
                    @elseif($twoType=='Number')
                        <?php
                            $value = explode('[!f2!]',$defArray[$i])[1];
                            $unit = \App\ComboListField::getComboFieldOption($field,'Unit','two');
                            if($unit!=null && $unit!=''){
                                $value .= ' '.$unit;
                            }
                        ?>
                        <span style="float:left;width:40%;margin-bottom:10px">{{$value}}</span>
                    @elseif($twoType=='Multi-Select List' | $twoType=='Generated List')
                        <?php
                            $value = explode('[!f2!]',$defArray[$i])[1];
                            $value = explode('[!]',$value);
                        ?>

                        <span style="float:left;width:40%;margin-bottom:10px">
                            @foreach($value as $val)
                                <div>{{$val}}</div>
                            @endforeach
                        </span>
                    @endif

                    <span class="delete_combo_def" style="float:left;width:20%;margin-bottom:10px"><a>[X]</a></span>
                </div>
            @endfor
        @endif
    </div>


    <div class="form-group">
        @include('partials.combofields.default_inputs',['field'=>$field, 'type'=>$oneType, 'fnum'=>'one'])
        @include('partials.combofields.default_inputs',['field'=>$field, 'type'=>$twoType, 'fnum'=>'two'])
        <br>
        <button type="button" class="btn btn-primary add_option">Add Default Value</button>
    </div>

    <br>

    <h4>{{trans('fields_options_combolist.options')}} {{ $oneName }}</h4>
    @if($oneType=='Text')
        @include('partials.combofields.text',['field'=>$field,'fnum'=>'one'])
    @elseif($oneType=='Number')
        @include('partials.combofields.number',['field'=>$field,'fnum'=>'one'])
    @elseif($oneType=='List')
        @include('partials.combofields.list',['field'=>$field,'fnum'=>'one'])
    @elseif($oneType=='Multi-Select List')
        @include('partials.combofields.mslist',['field'=>$field,'fnum'=>'one'])
    @elseif($oneType=='Generated List')
        @include('partials.combofields.genlist',['field'=>$field,'fnum'=>'one'])
    @endif

    <br>

    <h4>{{trans('fields_options_combolist.options')}} {{ $twoName }}</h4>
    @if($twoType=='Text')
        @include('partials.combofields.text',['field'=>$field,'fnum'=>'two'])
    @elseif($twoType=='Number')
        @include('partials.combofields.number',['field'=>$field,'fnum'=>'two'])
    @elseif($twoType=='List')
        @include('partials.combofields.list',['field'=>$field,'fnum'=>'two'])
    @elseif($twoType=='Multi-Select List')
        @include('partials.combofields.mslist',['field'=>$field,'fnum'=>'two'])
    @elseif($twoType=='Generated List')
        @include('partials.combofields.genlist',['field'=>$field,'fnum'=>'two'])
    @endif


    <div class="form-group">
        {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('partials.combofields.combo_option_preset')

    @include('errors.list')
@stop

@section('footer')

    <script>
        $('#combo_defaults').on('click', '.delete_combo_def', function() {
            parentDiv = $(this).parent();
            parentDiv.remove();
        });

        $('.form-group').on('click', '.add_option', function() {
            val1 = $('#default_one').val();
            val2 = $('#default_two').val();
            type1 = '{{$oneType}}';
            type2 = '{{$twoType}}';

            if(val1=='' | val2==''){
                console.log('Both fields must be filled out');
            }else{
                div = '<div class="default">';

                if(type1=='Text' | type1=='List'){
                    div += '<span style="float:left;width:40%;margin-bottom:10px">'+val1+'</span>';
                }else if(type1=='Number'){
                    unit = '<?php
                        if($oneType=='Number')
                            echo \App\ComboListField::getComboFieldOption($field,'Unit','one');
                    ?>';
                    div += '<span style="float:left;width:40%;margin-bottom:10px">'+val1+' '+unit+'</span>';
                }else if(type1=='Multi-Select List' | type1=='Generated List'){
                    div += '<span style="float:left;width:40%;margin-bottom:10px">';
                    for(k=0;k<val1.length;k++){
                        div += '<div>'+val1[k]+'</div>';
                    }
                    div += '</span>';
                }

                if(type2=='Text' | type2=='List'){
                    div += '<span style="float:left;width:40%;margin-bottom:10px">'+val2+'</span>';
                }else if(type2=='Number'){
                    unit = '<?php
                        if($twoType=='Number')
                            echo \App\ComboListField::getComboFieldOption($field,'Unit','two');
                    ?>';
                    div += '<span style="float:left;width:40%;margin-bottom:10px">'+val2+' '+unit+'</span>';
                }else if(type2=='Multi-Select List' | type2=='Generated List'){
                    div += '<span style="float:left;width:40%;margin-bottom:10px">';
                    for(k=0;k<val2.length;k++){
                        div += '<div>'+val2[k]+'</div>';
                    }
                    div += '</span>';
                }

                div += '<span class="delete_combo_def" style="float:left;width:20%;margin-bottom:10px"><a>[X]</a></span>';

                div += '</div>';

                $('#combo_defaults').html($('#combo_defaults').html()+div);

                if(type1=='Multi-Select List' | type1=='Generated List' | type1=='List')
                    $('#default_one').select2("val", "");
                else
                    $('#default_one').val('');

                if(type2=='Multi-Select List' | type2=='Generated List' | type2=='List')
                    $('#default_two').select2("val", "");
                else
                    $('#default_two').val('');
            }
        });

        function selectAll(){
            selectBox = $('.list_optionsone > option').each(function(){
                $(this).attr('selected', 'selected');
            });

            selectBox = $('.list_optionstwo > option').each(function(){
                $(this).attr('selected', 'selected');
            });

            type1 = '{{$oneType}}';
            type2 = '{{$twoType}}';

            valone = [];
            valtwo = [];

            $('.default').each(function(){
                var i=1;
                $(this).children().each(function(){
                    if(i==1){
                        if(type1=='Text' | type1=='List'){
                            val = $(this).text();
                            valone.push(val);
                        }else if(type1=='Number'){
                            val = $(this).text().split(" ")[0];
                            valone.push(val);
                        }else if(type1=='Multi-Select List' | type1=='Generated List'){
                            val = '';
                            $(this).children().each(function(){
                                if(val=='')
                                    val += ($(this).text());
                                else
                                    val += '[!]'+($(this).text());
                            });
                            valone.push(val);
                        }
                        i=2;
                    }else if(i==2){
                        if(type2=='Text' | type2=='List'){
                            val = $(this).text();
                            valtwo.push(val);
                        }else if(type2=='Number'){
                            val = $(this).text().split(" ")[0];
                            valtwo.push(val);
                        }else if(type2=='Multi-Select List' | type2=='Generated List'){
                            val = '';
                            $(this).children().each(function(){
                                if(val=='')
                                    val += ($(this).text());
                                else
                                    val += '[!]'+($(this).text());
                            });
                            valtwo.push(val);
                        }
                        return false;
                    }
                });
            });

            for(j=0;j<valone.length;j++) {
                var input1 = $("<input>")
                        .attr("type", "hidden")
                        .attr("name", "defvalone[]").val(valone[j]);
                var input2 = $("<input>")
                        .attr("type", "hidden")
                        .attr("name", "defvaltwo[]").val(valtwo[j]);
                $('#comboform').append($(input1));
                $('#comboform').append($(input2));
            }
        }
    </script>

@stop