@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <span><h1>{{trans('forms_show.import')}}</h1></span>

    <hr>

    <div id="upload_div">
        <div>
            <a href="{{ action('ImportController@exportSample',['pid' => $form->pid, 'fid' => $form->fid, 'type' => 'XML']) }}">[{{trans('forms_show.samplexml')}}]</a>
            <a href="{{ action('ImportController@exportSample',['pid' => $form->pid, 'fid' => $form->fid, 'type' => 'JSON']) }}">[{{trans('forms_show.samplejson')}}]</a>
        </div>

        <div class="form-group">
            {!! Form::label('xml', trans('forms_show.xml').': ') !!}
            {!! Form::file('xml', ['class' => 'form-control', 'id' => 'upload_xml', 'accept' => '.xml,.json']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('zip', trans('forms_show.zip').': ') !!}
            {!! Form::file('zip', ['class' => 'form-control', 'id' => 'upload_zip', 'accept' => '.zip']) !!}
        </div>

        <div class="form-group">
            <button type="button" class="form-control btn btn-primary" id="submit_files">{{trans('forms_show.submitfiles')}}</button>
        </div>
    </div>

    <div id="matchup_div">
    </div>

    <div id="counter_div" hidden>
        <div id="progress_text">0 of 0 Records Submitted</div>
        <div class="progress">
            <div id="progress_bar" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                <span class="sr-only">0% Complete</span>
            </div>
        </div>
    </div>

    <div id="error_div">
    </div>
@stop

@section('footer')
    <script>
        $('#upload_div').on('click', '#submit_files', function() {
            if ($('#upload_xml').val() != '') {
                fd = new FormData();
                fd.append("records",$('#upload_xml')[0].files[0]);
                var name = $('#upload_xml').val();
                fd.append('type',name.replace(/^.*\./, ''));
                if ($('#upload_zip').val() != '') {
                    fd.append("files", $('#upload_zip')[0].files[0]);
                }
                fd.append("fid","{{$form->fid}}");
                fd.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ action('ImportController@matchupFields',['pid'=>$form->pid,'fid'=>$form->fid])}}',
                    type: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        console.log(data);
                        $('#upload_div').attr('hidden','hidden');
                        $('#matchup_div').html(data['matchup']);

                        //initialize matchup
                        tags = [];
                        slugs = [];
                        table = {};
                        $('.tags').each(function(){
                            tags.push($(this).val());
                        });
                        $('.slugs').each(function(){
                            slugs.push($(this).val());
                        });
                        for(j=0; j<slugs.length; j++){
                            table[tags[j]] = slugs[j];
                        }

                        //initialize counter
                        done = 0;
                        succ = 0;
                        failed = [];
                        total = data['records'].length;
                        $('#progress_bar').attr('aria-valuemax',"100");
                        $('#progress_text').text(succ+' of '+total+' Records Submitted');

                        $('#matchup_div').on('click', '#submit_records', function() {
                            $('#matchup_div').attr('hidden','hidden');
                            $('#counter_div').removeAttr('hidden');

                            //foreach loop
                            for(i=0; i<total; i++){
                                //ajax to store record
                                $.ajax({
                                    url: '{{ action('ImportController@importRecord',['pid'=>$form->pid,'fid'=>$form->fid]) }}',
                                    type: 'POST',
                                    data: {
                                        "_token": "{{ csrf_token() }}",
                                        "record": data['records'][i],
                                        "table": table,
                                        "type": data['type']
                                    },
                                    success: function(data){
                                        console.log(data);
                                        //if success
                                        if(data=='') {
                                            succ++;
                                            $('#progress_text').text(succ+' of '+total+' Records Submitted');
                                        }
                                        //if error
                                        else{
                                            //list error message
                                            $('#error_div').html(data);
                                            //add obj to failed
                                            //failed.push(data['records'][i]);
                                        }
                                        done++;
                                        //update progress bar
                                        percent = (done/total)*100;
                                        bar = $('#progress_bar');
                                        bar.attr('aria-valuenow',percent);
                                        bar.attr('style','width:'+percent+'%');
                                        $('.sr-only').text(percent+'% Complete');
                                        $('#progress_text').text(done+' of '+total+' Records Submitted');

                                        //if done = total
                                        if(done==total) {
                                            //Display links for downloading bad xml
                                            //Display link to Go to Records Page
                                            $('#counter_div').append('<a href="{{ action('RecordController@index',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('records_show.records')}}]</a>');
                                            //console.log(failed);
                                        }
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    </script>
@stop