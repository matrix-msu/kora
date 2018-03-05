@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('default','Default Associations: ') !!}
        {!! Form::select('default[]', \App\AssociatorField::getAssociatorList($field), \App\AssociatorField::getAssociatorList($field),
            ['class' => 'multi-select assoc-default-records-js', 'multiple', "data-placeholder" => "Search below to add associated records"]) !!}
    </div>

    <div class="form-group mt-xs">
        {!! Form::label('search','Search Associations: ') !!}
        <input type="text" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)">
    </div>
    <div class="form-group mt-xs">
        {!! Form::label('search','Association Results: ') !!}
        {!! Form::select('search[]', [], null, ['class' => 'multi-select assoc-select-records-js', 'multiple',
            "data-placeholder" => "Select a record association to add to defaults"]) !!}

        <p class="sub-text mt-sm">
            Apply configuration below and update field for search to properly function
        </p>
    </div>

    <div class="form-group mt-xxxl">{!! Form::label('','Search Configuration: ') !!}</div>
    @foreach(\App\Http\Controllers\AssociationController::getAvailableAssociations($field->fid) as $a)
        <?php
            $f = \App\Http\Controllers\FormController::getForm($a->dataForm);
            $formFieldsData = \App\Field::where('fid','=',$f->fid)->get()->all();
            $formFields = array();
            foreach($formFieldsData as $fl) {
                $formFields[$fl->flid] = $fl->name;
            }

            //get layout info for this form
            if(array_key_exists($f->fid,$opt_layout)){
                $f_check = $opt_layout[$f->fid]['search'];
                $f_flids = $opt_layout[$f->fid]['flids'];
            }else{
                $f_check = false;
                $f_flids = null;
            }

            $selectArray = ['class' => 'single-select', "data-placeholder" => "Select field preview value", 'disabled'];
            if($f_check)
                $selectArray = ['class' => 'single-select', "data-placeholder" => "Select field preview value"];
        ?>
        <div class="form-group mt-xl">
            <div class="check-box-half">
                <input type="checkbox" value="1" id="active" class="check-box-input association-check-js" name="checkbox_{{$f->fid}}"
                   @if($f_check)
                       checked
                   @endif
                />
                <span class="check"></span>
                <span class="placeholder">Search through {{$f->name}}?</span>
            </div>
        </div>

        <div class="form-group mt-m
        @if(!$f_check)
         hidden
        @endif
        ">
            {!! Form::label('preview_'.$f->fid, 'Preview Value: ') !!}
            {!! Form::select('preview_'.$f->fid, $formFields, $f_flids, $selectArray) !!}
        </div>
    @endforeach
@stop

@section('fieldOptionsJS')
    assocSearchURI = "{{ action('AssociatorSearchController@assocSearch',['pid' => $field->pid,'fid'=>$field->fid, 'flid'=>$field->flid]) }}";
    csfrToken = "{{ csrf_token() }}";

    Kora.Fields.Options('Associator');
@stop