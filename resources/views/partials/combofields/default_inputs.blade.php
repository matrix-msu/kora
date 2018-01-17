{!! Form::label('default_'.$fnum,\App\ComboListField::getComboFieldName($field,$fnum).': ') !!}
@if($type=='Text')
    @if(\App\ComboListField::getComboFieldOption($field,'MultiLine',$fnum)==0)
        {!! Form::text('default_'.$fnum, null, ['class' => 'form-control', 'id' => 'default_'.$fnum]) !!}
    @elseif(\App\ComboListField::getComboFieldOption($field,'MultiLine',$fnum)==1)
        {!! Form::textarea('default_'.$fnum, null, ['class' => 'form-control']) !!}
    @endif
@elseif($type=='Number')
    <input
            type="number" id="default_{{$fnum}}" name="default_{{$fnum}}" class="form-control" value=""
            step="{{ \App\ComboListField::getComboFieldOption($field, "Increment", $fnum) }}"
            min="{{ \App\ComboListField::getComboFieldOption($field, "Min", $fnum) }}"
            max="{{ \App\ComboListField::getComboFieldOption($field, "Max", $fnum) }}">
@elseif($type=='List')
    {!! Form::select('default_'.$fnum,\App\ComboListField::getComboList($field,true,$fnum), null,['class' => 'form-control', 'id' => 'default_'.$fnum]) !!}
    <script>
        $('#default_{{$fnum}}').select2();
    </script>
@elseif($type=='Multi-Select List')
    {!! Form::select('default_'.$fnum.'[]',\App\ComboListField::getComboList($field,false,$fnum), null,['class' => 'form-control', 'multiple', 'id' => 'default_'.$fnum]) !!}
    <script>
        $('#default_{{$fnum}}').select2();
    </script>
@elseif($type=='Generated List')
    {!! Form::select('default_'.$fnum.'[]',\App\ComboListField::getComboList($field,false,$fnum), null,['class' => 'form-control', 'multiple', 'id' => 'default_'.$fnum]) !!}
    <script>
        $('#default_{{$fnum}}').select2({
            tags: true
        });
    </script>
@elseif($type=='Associator')
    <div class="form-group">
        <input type="text" id="assocSearch_{{$fnum}}" class="form-control" placeholder="Enter search term to find records..."/>
        <div style="display:none;" id="search_progress_{{$fnum}}" class="progress">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
                {{trans('update_index.loading')}}
            </div>
        </div>
        <div id="assocPages_{{$fnum}}">
        </div>
        <div id="assocSearchResults_{{$fnum}}">
        </div>
        {!! Form::select($field->flid.'[]', [],  null, ['class' => 'form-control', 'multiple', 'id' => 'default_'.$fnum]) !!}
        <button type="button" class="btn btn-primary remove_option_{{$fnum}}">{{trans('fields_options_list.delete')}}</button>
        <button type="button" class="btn btn-primary move_option_up_{{$fnum}}">{{trans('fields_options_list.up')}}</button>
        <button type="button" class="btn btn-primary move_option_down_{{$fnum}}">{{trans('fields_options_list.down')}}</button>
    </div>

    <script>
        $('#assocSearch_{{$fnum}}').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();

                var assocText = $('#assocSearch_{{$fnum}}');
                var loadbar = $('#search_progress_{{$fnum}}');

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
                        "keyword": keyword,
                        "combo": '{{$fnum}}'
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
                                html += "<div id='{{$field->flid}}pg1_{{$fnum}}' class='aPage'>";
                            }else if(cnt == 1){
                                html += "<div id='{{$field->flid}}pg"+page+"_{{$fnum}}' class='aPage' style='display:none'>";
                            }
                            //print out results
                            if(cnt%2==1)
                                html += "<div class='result_div'>";
                            else
                                html += "<div class='result_div' style='background-color: lightgrey'>";
                            html += "<span class='result_kid_{{$fnum}}' style='float: left; width: 33%;'>"+index+"</span>";
                            //html += "<span style='display: inline-block; width: 33%;'>"+records[index]+"</span>";
                            var preview = records[index];
                            html += "<span style='display: inline-block; width: 33%;'>"+preview[0];
                            for(var j=1;j<preview.length;j++) {
                                html += "<br>"+preview[j];
                            }
                            html += "</span>";
                            html += "<span class='result_add_{{$fnum}}' style='float: right; width: 33%;'><a>Add</a></span></div>";
                        }
                        //case where the last page has less than 10 records
                        if(cnt != 1){html += '</div>';}

                        //case where no results
                        if(cnt==0 && page==1){
                            html += "<div id='{{$field->flid}}pg1_{{$fnum}}' class='aPage'>No results found...</div>";
                        }

                        $('#assocSearchResults_{{$fnum}}').html(html);

                        //adding the pagination links if more than one page
                        if(page>1){
                            pageHTML = '';
                            for(var i=1;i<page+1;i++){
                                pageHTML += "<button type='button' class='page_{{$fnum}}' style='margin-right:5px'>"+i+"</button>";
                            }
                            $('#assocPages_{{$fnum}}').html(pageHTML);
                        }else{
                            $('#assocPages_{{$fnum}}').html('');
                        }
                    }
                });

                return false;
            }
        });

        $('#assocPages_{{$fnum}}').on('click', '.page_{{$fnum}}', function() {
            var page = '{{$field->flid}}pg_{{$fnum}}'+$(this).text();
            $('.aPage').each(function(){
                var pgid = $(this).attr('id');
                if(pgid==page){
                    $(this).attr('style','');
                }else{
                    $(this).attr('style','display:none');
                }
            });
        });

        $('#assocSearchResults_{{$fnum}}').on('click', '.result_add_{{$fnum}}', function() {
            var kid = $(this).siblings('.result_kid_{{$fnum}}').text();

            var html = "<option value='"+kid+"' selected>"+kid+"</option>";
            var options = $('#default_{{$fnum}}').html()+html;

            $('#default_{{$fnum}}').html(options);
        });
        $('.form-group').on('click', '.remove_option_{{$fnum}}', function(){
            val = $('option:selected', '#default_{{$fnum}}').val();

            $('option:selected', '#default_{{$fnum}}').remove();
        });
        $('.form-group').on('click', '.move_option_up_{{$fnum}}', function(){
            val = $('option:selected', '#default_{{$fnum}}').val();

            $('#default_{{$fnum}}').find('option:selected').each(function() {
                $(this).insertBefore($(this).prev());
            });
        });
        $('.form-group').on('click', '.move_option_down_{{$fnum}}', function(){
            val = $('option:selected', '#default_{{$fnum}}').val();

            $('#default_{{$fnum}}').find('option:selected').each(function() {
                $(this).insertAfter($(this).next());
            });
        });
    </script>
@endif