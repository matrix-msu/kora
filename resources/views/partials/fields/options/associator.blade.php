@extends('fields.show')

@section('fieldOptions')
    <div class="form-group default_div">
        {!! Form::label('default',trans('fields_options_associator.def').': ') !!}
        <input type="text" id="assocSearch" class="form-control" placeholder="Enter search term to find records..."/>
        <div style="display:none;" id="search_progress" class="progress">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
                {{trans('update_index.loading')}}
            </div>
        </div>
        <div id="assocPages">
        </div>
        <div id="assocSearchResults">
        </div>
        {!! Form::select('default[]',\App\AssociatorField::getAssociatorList($field),  null, ['class' => 'form-control', 'multiple', 'id' => 'default']) !!}
        <button type="button" class="btn btn-primary remove_option">{{trans('fields_options_list.delete')}}</button>
        <button type="button" class="btn btn-primary move_option_up">{{trans('fields_options_list.up')}}</button>
        <button type="button" class="btn btn-primary move_option_down">{{trans('fields_options_list.down')}}</button>
    </div>

    <div id="assoc_permissions">
        {!! Form::label('forms',trans('fields_options_associator.assoc').': ') !!}
        {!! Form::hidden('searchforms','', ['id' => 'assocValue']) !!}
        <div class="assoc_item_titles">
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
            <div class="assoc_item" id="{{$f->fid}}">
                <span style="float: left; width: 33%;">{{$f->name}}</span>
                <span style="display: inline-block; width: 33%;">
                    {!! Form::checkbox("checkbox".$f->fid,0,$f_check,['class' => 'form-control assoc_search', 'id' => 'search'.$f->fid]) !!}
                </span>
                <span style="float: right; width: 33%;">
                    {!! Form::select('preview[]',$formFields, $f_flids, ['class' => 'form-control assoc_preview', 'multiple', 'id' => 'preview'.$f->fid]) !!}
                </span>
            </div>
            <script>
                $('#preview{{$f->fid}}').select2();
            </script>
        @endforeach
    </div>
@stop

@section('fieldOptionsJS')
    assocSearchURI = "{{ action('AssociatorSearchController@assocSearch',['pid' => $field->pid,'fid'=>$field->fid, 'flid'=>$field->flid]) }}";
    csfrToken = "{{ csrf_token() }}";

    Kora.Fields.Options('Associator');
@stop