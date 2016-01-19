@extends('fields.show')

@section('fieldOptions')

    <?php
            //we are building an array about the association permissions to populate the layout
            $option = \App\Http\Controllers\FieldController::getFieldOption($field,'SearchForms');
            $options = explode('[!]',$option);
            $opt_layout = array();

            foreach($options as $opt){
                $opt_fid = explode('[fid]',$opt)[1];
                $opt_search = explode('[search]',$opt)[1];
                $opt_flids = explode('[flids]',$opt)[1];
                $opt_flids = explode('-',$opt_flids);

                $opt_layout[$opt_fid] = ['search' => $opt_search, 'flids' => $opt_flids];
            }
    ?>

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_associator.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_associator.updatereq'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateDefault', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <?php
            $assocRecords = array();

            $assocs = \App\Http\Controllers\AssociationController::getAvailableAssociations($field->fid);
            foreach($assocs as $a){
                $records = \App\Record::where('fid','=',$a->dataForm)->get()->all();
                $kids = array();
                foreach($records as $rec){
                    $kids[$rec->kid] = $rec->kid;
                }
                $assocRecords = array_merge($assocRecords,$kids);
            }
    ?>
    <div class="form-group">
        {!! Form::label('default',trans('fields_options_associator.def').': ') !!}
        {!! Form::select('default[]',$assocRecords, explode('[!]',$field->default), ['class' => 'form-control', 'multiple', 'id' => 'default']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_associator.updatedef'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    <div id="assoc_permissions">
        {!! Form::label('forms',trans('fields_options_associator.assoc').': ') !!}
        <div class="assoc_item_titles">
            <span style="float: left; width: 33%;"><b>{{trans('fields_options_associator.form')}}</b></span>
            <span style="display: inline-block; width: 33%;"><b>{{trans('fields_options_associator.search')}}</b></span>
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

    @include('errors.list')
@stop

@section('footer')

    <script>
        $('#default').select2();

        $('.assoc_item').on('click','.assoc_search', function(){
            saveAssocList();
        });

        $('.assoc_item').on('change','.assoc_preview', function(){
            saveAssocList();
        });

        function saveAssocList(){
            //foreach assoc_item
            list = '';
            $('.assoc_item').each(function( index, element ) {
                fid = $(this).attr('id');
                //if checked or if preview fields has selections
                if($('#search'+fid).prop('checked') | $('#preview'+fid).val()!=null) {
                    //gather info and add to array
                    search = $('#search'+fid).prop('checked');
                    preview = $('#preview'+fid).val();

                    pOne = '[fid]'+fid+'[fid]';

                    if(search){
                        pTwo = '[search]1[search]';
                    }else{
                        pTwo = '[search]0[search]';
                    }

                    if(preview != null){
                        pThree = '[flids]'+preview[0];
                        for(var i=1;i<preview.length;i++){
                            pThree += '-'+preview[i];
                        }
                        pThree += '[flids]';
                    }else{
                        pThree = '[flids][flids]';
                    }

                    item = pOne+pTwo+pThree;

                    if(list==''){
                        list = item;
                    }else{
                        list += '[!]'+item;
                    }
                }
            });
            //send array to updateOptions url
            $.ajax({
                //We manually create the link in a cheap way because the JS isn't aware of the pid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('FieldController@updateOptions',[$field->pid, $field->fid, $field->flid]) }}',
                type: 'PATCH',
                data: {
                    "_token": "{{ csrf_token() }}",
                    option: "SearchForms",
                    value: list
                },
                success: function (result) {
                    console.log('success');
                }
            });
        }
    </script>

@stop