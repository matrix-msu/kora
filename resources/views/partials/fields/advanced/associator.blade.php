{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('','Search Configuration: ') !!}
    <p class="sub-text">
        If no forms are available, have a Form Admin request permission to forms by using the Association Permissions page
    </p>
</div>
@foreach(\App\Http\Controllers\AssociationController::getAvailableAssociations($fid) as $a)
    <?php
    $f = \App\Http\Controllers\FormController::getForm($a->dataForm);
    $formFieldsData = \App\Field::where('fid','=',$f->fid)->get()->all();
    $formFields = array();
    foreach($formFieldsData as $fl) {
        $formFields[$fl->flid] = $fl->name;
    }

    $selectArray = ['class' => 'single-select', "data-placeholder" => "Select field preview value", 'disabled'];
    ?>
    <div class="form-group mt-xl">
        <div class="check-box-half">
            <input type="checkbox" value="1" id="active" class="check-box-input association-check-js" name="checkbox_{{$f->fid}}"/>
            <span class="check"></span>
            <span class="placeholder">Search through {{$f->name}}?</span>
        </div>
    </div>

    <div class="form-group mt-m hidden">
        {!! Form::label('preview_'.$f->fid, 'Preview Value: ') !!}
        {!! Form::select('preview_'.$f->fid, $formFields, null, $selectArray) !!}
    </div>
@endforeach

<script>
    Kora.Fields.Options('Associator');
</script>