<?php
//we are building an array about the association permissions to populate the layout
$option = \App\ComboListField::getComboFieldOption($field,'SearchForms',$fnum);
$opt_layout = array();
if($option!=''){
    $options = explode('[!]',$option);

    foreach($options as $opt){
        $opt_fid = explode('[fid]',$opt)[1];
        $opt_search = explode('[search]',$opt)[1];
        $opt_flids = explode('[flids]',$opt)[1];
        $opt_flids = explode('-',$opt_flids);

        $opt_layout[$opt_fid] = ['search' => $opt_search, 'flids' => $opt_flids];
    }
}
?>

<div id="assoc_permissions{{$fnum}}">
    {!! Form::label('forms',trans('fields_options_associator.assoc').': ') !!}
    {!! Form::hidden('searchforms','', ['id' => 'assocValue'.$fnum]) !!}
    <div class="assoc_item_titles{{$fnum}}">
        <span style="float: left; width: 33%;"><b>{{trans('fields_options_associator.form')}}</b></span>
        <span style="display: inline-block; width: 33%;"><b>{{trans('fields_options_associator.fsearch')}}</b></span>
        <span style="float: right; width: 33%;"><b>{{trans('fields_options_associator.preview')}}</b></span>
    </div>
    @foreach(\App\Http\Controllers\AssociationController::getAvailableAssociations($field->fid) as $a)
        <?php
        $f = \App\Http\Controllers\FormController::getForm($a->dataForm);
        $formFieldsData = \App\Field::where('fid','=',$f->fid)->get()->all();
        $formFields = array();
        foreach($formFieldsData as $fl){
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
        ?>
        <div class="assoc_item{{$fnum}}" id="{{$f->fid}}_{{$fnum}}">
            <span style="float: left; width: 33%;">{{$f->name}}</span>
            <span style="display: inline-block; width: 33%;">
                {!! Form::checkbox('checkbox_'.$f->fid.'_'.$fnum,0,$f_check,['class' => 'form-control assoc_search', 'id' => 'search_'.$f->fid.'_'.$fnum]) !!}
            </span>
            <span style="float: right; width: 33%;">
                {!! Form::select('preview_'.$f->fid.'_'.$fnum.'[]',$formFields, $f_flids, ['class' => 'form-control assoc_preview', 'multiple', 'id' => 'preview_'.$f->fid.'_'.$fnum]) !!}
            </span>
        </div>
        <script>
            $('#preview_{{$f->fid}}_{{$fnum}}').select2();
        </script>
    @endforeach
</div>