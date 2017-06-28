@extends('fields.show')

@section('fieldOptions')

    <?php
            //we are building an array about the association permissions to populate the layout
            $option = \App\Http\Controllers\FieldController::getFieldOption($field,'SearchForms');
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

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldAjaxController@updateOptions', $field->pid, $field->fid, $field->flid, $field->type], 'onsubmit' => 'saveAssocList()']) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_associator.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('searchable',trans('fields_options_associator.search').': ') !!}
        {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extsearch',trans('fields_options_associator.extsearch').': ') !!}
        {!! Form::select('extsearch',['false', 'true'], $field->extsearch, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewable',trans('fields_options_associator.viewable').': ') !!}
        {!! Form::select('viewable',['false', 'true'], $field->viewable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewresults',trans('fields_options_associator.viewresults').': ') !!}
        {!! Form::select('viewresults',['false', 'true'], $field->viewresults, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extview',trans('fields_options_associator.extview').': ') !!}
        {!! Form::select('extview',['false', 'true'], $field->extview, ['class' => 'form-control']) !!}
    </div>

    <hr>

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
        {!! Form::select('default[]',\App\AssociatorField::getDefault($field->default,false),  null, ['class' => 'form-control', 'multiple', 'id' => 'default']) !!}
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
    <br>
    <div class="form-group">
        {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('#assocSearch').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();

                var assocText = $('#assocSearch');
                var loadbar = $('#search_progress');

                //get value
                var keyword = assocText.val();

                //hide value and display loading
                assocText.hide();
                loadbar.show();

                //send it to ajax
                $.ajax({
                    url: "{{ action('AssociatorSearchController@assocSearch',['pid' => $field->pid,'fid'=>$field->fid, 'flid'=>$field->flid]) }}",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "keyword": keyword
                    },
                    success: function (result) {
                        assocText.show();
                        loadbar.hide();

                        var records = result;
                        var html = '';

                        var cnt = 0;
                        var page = 1;
                        for (var index in records) {
                            //close pagination
                            if(cnt==10){
                                html += "</div>";
                                //next page
                                cnt = 0;
                                page++;
                            }

                            cnt++;

                            //setup pagination
                            if(cnt == 1 && page==1){
                                html += "<div id='pg1' class='aPage'>";
                            }else if(cnt == 1){
                                html += "<div id='pg"+page+"' class='aPage' style='display:none'>";
                            }
                            //print out results
                            if(cnt%2==1)
                                html += "<div class='result_div'>";
                            else
                                html += "<div class='result_div' style='background-color: lightgrey'>";
                            html += "<span class='result_kid' style='float: left; width: 33%;'>"+index+"</span>";
                            //html += "<span style='display: inline-block; width: 33%;'>"+records[index]+"</span>";
                            var preview = records[index];
                            html += "<span style='display: inline-block; width: 33%;'>"+preview[0];
                            for(var j=1;j<preview.length;j++) {
                                html += "<br>"+preview[j];
                            }
                            html += "</span>";
                            html += "<span class='result_add' style='float: right; width: 33%;'><a>Add</a></span></div>";
                        }
                        //case where the last page has less than 10 records
                        if(cnt != 1){html += '</div>';}

                        //case where no results
                        if(cnt==0 && page==1){
                            html += "<div id='pg1' class='aPage'>No results found...</div>";
                        }

                        $('#assocSearchResults').html(html);

                        //adding the pagination links if more than one page
                        if(page>1){
                            pageHTML = '';
                            for(var i=1;i<page+1;i++){
                                pageHTML += "<button type='button' class='page' style='margin-right:5px'>"+i+"</button>";
                            }
                            $('#assocPages').html(pageHTML);
                        }else{
                            $('#assocPages').html('');
                        }
                    }
                });

                return false;
            }
        });

        $('#assocPages').on('click', '.page', function() {
            var page = 'pg'+$(this).text();
            $('.aPage').each(function(){
                var pgid = $(this).attr('id');
                if(pgid==page){
                    $(this).attr('style','');
                }else{
                    $(this).attr('style','display:none');
                }
            });
        });

        $('#assocSearchResults').on('click', '.result_add', function() {
            var kid = $(this).siblings('.result_kid').text();

            var html = "<option value='"+kid+"' selected>"+kid+"</option>";
            var options = $('#default').html()+html;

            $('#default').html(options);
        });
        $('.default_div').on('click', '.remove_option', function(){
            val = $('option:selected', '#default').val();

            $('option:selected', '#default').remove();
        });
        $('.default_div').on('click', '.move_option_up', function(){
            val = $('option:selected', '#default').val();

            $('#default').find('option:selected').each(function() {
                $(this).insertBefore($(this).prev());
            });
        });
        $('.default_div').on('click', '.move_option_down', function(){
            val = $('option:selected', '#default').val();

            $('#default').find('option:selected').each(function() {
                $(this).insertAfter($(this).next());
            });
        });

        function saveAssocList(){
            //foreach assoc_item
            var list = '';
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

            $('#assocValue').val(list);

            $("#default > option").each(function() {
                $(this).attr('selected','selected');
            });
        }
    </script>

@stop