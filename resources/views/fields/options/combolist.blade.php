@extends('fields.show')

@section('fieldOptions')
    <?php
            $oneType = \App\Http\Controllers\FieldController::getComboFieldType($field,'one');
            $twoType = \App\Http\Controllers\FieldController::getComboFieldType($field,'two');

            $defs = $field->default;
            $defArray = explode('[!def!]',$defs);
    ?>

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_combolist.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_combolist.updatereq'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}


    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboName', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('fieldnum','one') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_combolist.nameone').': ') !!}
        {!! Form::text('value',\App\Http\Controllers\FieldController::getComboFieldName($field,'one'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_combolist.updateone'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboName', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('fieldnum','two') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_combolist.nametwo').': ') !!}
        {!! Form::text('value',\App\Http\Controllers\FieldController::getComboFieldName($field,'two'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_combolist.updatetwo'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}


    <div id="combo_defaults" style="overflow: auto">
        <div>
            <span style="float:left;width:40%;margin-bottom:10px"><b>{{\App\Http\Controllers\FieldController::getComboFieldName($field,'one')}}</b></span>
            <span style="float:left;width:40%;margin-bottom:10px"><b>{{\App\Http\Controllers\FieldController::getComboFieldName($field,'two')}}</b></span>
            <span style="float:left;width:20%;margin-bottom:10px"><b>{{trans('fields_options_combolist.remove')}}</b></span>
        </div>
        @if($defs!=null && $defs!='')
            @for($i=0;$i<sizeof($defArray);$i++)
                <div id="{{$i}}">
                    @if($oneType=='Text' | $oneType=='List')
                        <?php $value = explode('[!f1!]',$defArray[$i])[1]; ?>
                        <span style="float:left;width:40%;margin-bottom:10px">{{$value}}</span>
                    @elseif($oneType=='Number')
                        <?php
                            $value = explode('[!f1!]',$defArray[$i])[1];
                            $unit = \App\Http\Controllers\FieldController::getComboFieldOption($field,'Unit','one');
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
                            $unit = \App\Http\Controllers\FieldController::getComboFieldOption($field,'Unit','two');
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

                    <span class="delete_combo_def" id="{{$i}}" style="float:left;width:20%;margin-bottom:10px"><a>[X]</a></span>
                </div>
            @endfor
        @endif
    </div>


    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboDefault', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        @include('partials.combofields.default_inputs',['field'=>$field, 'type'=>$oneType, 'fnum'=>'one'])
        @include('partials.combofields.default_inputs',['field'=>$field, 'type'=>$twoType, 'fnum'=>'two'])
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_combolist.adddef'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    <br>

    <h4>{{trans('fields_options_combolist.options')}} {{ \App\Http\Controllers\FieldController::getComboFieldName($field,'one') }}</h4>
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

    <h4>{{trans('fields_options_combolist.options')}} {{ \App\Http\Controllers\FieldController::getComboFieldName($field,'two') }}</h4>
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

    @include('errors.list')
@stop

@section('footer')

    <script>
        $('#combo_defaults').on('click', '.delete_combo_def', function() {
            comID = $(this).attr('id');
            parentDiv = $(this).parent();

            $.ajax({
                url: '{{ action('FieldController@removeComboDefault',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    comID: comID
                },
                success: function (result) {
                    parentDiv.remove();
                }
            });
        });
    </script>

@stop