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
                    @elseif($oneType=='Multi-Select List' | $oneType=='Generated List' | $oneType=='Associator')
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
                    @elseif($twoType=='Multi-Select List' | $twoType=='Generated List' | $oneType=='Associator')
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
    @elseif($oneType=='Associator')
        @include('partials.combofields.associator',['field'=>$field,'fnum'=>'one'])
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
    @elseif($twoType=='Associator')
        @include('partials.combofields.associator',['field'=>$field,'fnum'=>'two'])
    @endif
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Combo List');
@stop