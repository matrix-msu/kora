<div class="form-group mt-xxxl">{!! Form::label('','Search Configuration') !!}</div>
@foreach(\App\Http\Controllers\AssociationController::getAvailableAssociations($field->fid) as $a)
    <?php
    $f = \App\Http\Controllers\FormController::getForm($a->dataForm);
    $formFieldsData = \App\Field::where('fid','=',$f->fid)->get()->all();
    $formFields = array();
    foreach($formFieldsData as $fl) {
        $formFields[$fl->flid] = $fl->name;
    }

    //get layout info for this form
    if(array_key_exists($f->fid,${"opt_layout_$fnum"})){
        $f_check = ${"opt_layout_$fnum"}[$f->fid]['search'];
        $f_flids = ${"opt_layout_$fnum"}[$f->fid]['flids'];
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
            <input type="checkbox" value="1" id="active" class="check-box-input association-check-js" name="checkbox_{{$f->fid}}_{{$fnum}}"
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
        {!! Form::label('preview_'.$f->fid.'_'.$fnum, 'Preview Value') !!}
        {!! Form::select('preview_'.$f->fid.'_'.$fnum, $formFields, $f_flids, $selectArray) !!}
    </div>
@endforeach