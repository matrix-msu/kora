<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            <div class="panel-body">
                <label for="{{$field->flid}}_input">{{trans('advanced_search.search_assoc')}}:</label></br>
                <input type="text" id="{{$field->flid}}_assocSearch" class="form-control" placeholder="Enter search term to find records..."/>
                <div style="display:none;" id="{{$field->flid}}_search_progress" class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
                        {{trans('update_index.loading')}}
                    </div>
                </div>
                <div id="{{$field->flid}}_assocPages">
                </div>
                <div id="{{$field->flid}}_assocSearchResults">
                </div>
                {!! Form::select( $field->flid . "_input[]", array(), "", ["class" => "form-control", "Multiple", 'id' => $field->flid."_input", "style" => "width: 100%"]) !!}
                <button type="button" class="btn btn-primary {{$field->flid}}_remove_option">{{trans('fields_options_list.delete')}}</button>
                {{trans('advanced_search.input_text')}}: <span id="{{$field->flid}}_valid_selection">{{trans('advanced_search.invalid')}}</span>.
            </div>
        </div>
    </div>
    <input type="hidden" id="{{$field->flid}}_valid" name="{{$field->flid}}_valid" value="0">
</div>
<script>
    $("#{{$field->flid}}_input").change(function() {
        if (this.value == "") {
            $("#{{$field->flid}}_valid_selection").html("{{trans('advanced_search.invalid')}}");
            $("#{{$field->flid}}_valid").val("0");
        }
        else {
            $("#{{$field->flid}}_valid_selection").html("{{trans('advanced_search.valid')}}");
            $("#{{$field->flid}}_valid").val("1");
        }
    });

    $('#{{$field->flid}}_assocSearch').on('keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();

            var assocText = $('#{{$field->flid}}_assocSearch');
            var loadbar = $('#{{$field->flid}}_search_progress');

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

                    $('#{{$field->flid}}_assocSearchResults').html(html);

                    //adding the pagination links if more than one page
                    if(page>1){
                        pageHTML = '';
                        for(var i=1;i<page+1;i++){
                            pageHTML += "<button type='button' class='page' style='margin-right:5px'>"+i+"</button>";
                        }
                        $('#{{$field->flid}}_assocPages').html(pageHTML);
                    }else{
                        $('#{{$field->flid}}_assocPages').html('');
                    }
                }
            });

            return false;
        }
    });

    $('#{{$field->flid}}_assocPages').on('click', '.page', function() {
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

    $('#{{$field->flid}}_assocSearchResults').on('click', '.result_add', function() {
        var kid = $(this).siblings('.result_kid').text();

        var html = "<option value='"+kid+"' selected>"+kid+"</option>";
        var options = $('#{{$field->flid}}_input').html()+html;

        $('#{{$field->flid}}_input').html(options);

        if ($('#{{$field->flid}}_input').value == "") {
            $("#{{$field->flid}}_valid_selection").html("{{trans('advanced_search.invalid')}}");
            $("#{{$field->flid}}_valid").val("0");
        }
        else {
            $("#{{$field->flid}}_valid_selection").html("{{trans('advanced_search.valid')}}");
            $("#{{$field->flid}}_valid").val("1");
        }

        validateForm($("[name=advanced_search]"));
    });
</script>