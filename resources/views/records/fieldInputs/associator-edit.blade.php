<div class="form-group">
    <?php
        if($associator==null){
            $options = array();
        }else{
            $options = array();
            $values = $associator->records()->get();
            foreach($values as $value){
                $aRec = \App\Http\Controllers\RecordController::getRecord($value->record);
                $options[$aRec->kid] = $aRec->kid;
            }
        }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    <input type="text" id="assocSearch{{$field->flid}}" class="form-control" placeholder="Enter search term to find records..."/>
    <div style="display:none;" id="search_progress_{{$field->flid}}" class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
            {{trans('update_index.loading')}}
        </div>
    </div>
    <div id="assocPages{{$field->flid}}">
    </div>
    <div id="assocSearchResults{{$field->flid}}">
    </div>
    {!! Form::select($field->flid.'[]',$options,  $options, ['class' => 'form-control', 'multiple', 'id' => $field->flid]) !!}
    <button type="button" class="btn btn-primary remove_option{{$field->flid}}">{{trans('fields_options_list.delete')}}</button>
    <button type="button" class="btn btn-primary move_option_up{{$field->flid}}">{{trans('fields_options_list.up')}}</button>
    <button type="button" class="btn btn-primary move_option_down{{$field->flid}}">{{trans('fields_options_list.down')}}</button>
</div>

<script>
    $('#assocSearch{{$field->flid}}').on('keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();

            var assocText = $('#assocSearch{{$field->flid}}');
            var loadbar = $('#search_progress_{{$field->flid}}');

            //get value
            var keyword = assocText.val();

            //hide value and display loading
            assocText.hide();
            loadbar.show();

            //send it to ajax
            $.ajax({
                url: "{{ action('FieldAjaxController@assocSearch',['pid' => $field->pid,'fid'=>$field->fid, 'flid'=>$field->flid]) }}",
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
                            html += "<div id='{{$field->flid}}pg1' class='aPage'>";
                        }else if(cnt == 1){
                            html += "<div id='{{$field->flid}}pg"+page+"' class='aPage' style='display:none'>";
                        }
                        //print out results
                        if(cnt%2==1)
                            html += "<div class='result_div'>";
                        else
                            html += "<div class='result_div' style='background-color: lightgrey'>";
                        html += "<span class='result_kid{{$field->flid}}' style='float: left; width: 33%;'>"+index+"</span>";
                        //html += "<span style='display: inline-block; width: 33%;'>"+records[index]+"</span>";
                        var preview = records[index];
                        html += "<span style='display: inline-block; width: 33%;'>"+preview[0];
                        for(var j=1;j<preview.length;j++) {
                            html += "<br>"+preview[j];
                        }
                        html += "</span>";
                        html += "<span class='result_add{{$field->flid}}' style='float: right; width: 33%;'><a>Add</a></span></div>";
                    }
                    //case where the last page has less than 10 records
                    if(cnt != 1){html += '</div>';}

                    //case where no results
                    if(cnt==0 && page==1){
                        html += "<div id='{{$field->flid}}pg1' class='aPage'>No results found...</div>";
                    }

                    $('#assocSearchResults{{$field->flid}}').html(html);

                    //adding the pagination links if more than one page
                    if(page>1){
                        pageHTML = '';
                        for(var i=1;i<page+1;i++){
                            pageHTML += "<button type='button' class='page{{$field->flid}}' style='margin-right:5px'>"+i+"</button>";
                        }
                        $('#assocPages{{$field->flid}}').html(pageHTML);
                    }else{
                        $('#assocPages{{$field->flid}}').html('');
                    }
                }
            });

            return false;
        }
    });

    $('#assocPages{{$field->flid}}').on('click', '.page{{$field->flid}}', function() {
        var page = '{{$field->flid}}pg'+$(this).text();
        $('.aPage').each(function(){
            var pgid = $(this).attr('id');
            if(pgid==page){
                $(this).attr('style','');
            }else{
                $(this).attr('style','display:none');
            }
        });
    });

    $('#assocSearchResults{{$field->flid}}').on('click', '.result_add{{$field->flid}}', function() {
        var kid = $(this).siblings('.result_kid{{$field->flid}}').text();

        var html = "<option value='"+kid+"' selected>"+kid+"</option>";
        var options = $('#{{$field->flid}}').html()+html;

        $('#{{$field->flid}}').html(options);
    });
    $('.form-group').on('click', '.remove_option{{$field->flid}}', function(){
        val = $('option:selected', '#{{$field->flid}}').val();

        $('option:selected', '#{{$field->flid}}').remove();
    });
    $('.form-group').on('click', '.move_option_up{{$field->flid}}', function(){
        val = $('option:selected', '#{{$field->flid}}').val();

        $('#{{$field->flid}}').find('option:selected').each(function() {
            $(this).insertBefore($(this).prev());
        });
    });
    $('.form-group').on('click', '.move_option_down{{$field->flid}}', function(){
        val = $('option:selected', '#{{$field->flid}}').val();

        $('#{{$field->flid}}').find('option:selected').each(function() {
            $(this).insertAfter($(this).next());
        });
    });
</script>