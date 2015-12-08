@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $pid])
    @include('partials.menu.form', ['pid' => $pid, 'fid' => $fid])
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <span><h3>Linked Open Data</h3></span>
                        <hr>

                        <div>
                            <b class="pull-left">Field</b>
                            <b class="pull-right">Linked Open Data</b>
                        </div>
                        <br>

                        @include('forms.layout.logic',['form'=>$form,'fieldview' => 'metadata.fieldview'])

                        <hr>
                        <div class="checkbox">
                            <label>
                            @if ($form->public_metadata == true)
                                <input id="public_metadata" name="public_metadata" type="checkbox" value="true" checked>
                            @else
                                <input id="public_metadata" name="public_metadata" type="checkbox" value="true">
                            @endif
                                Linked Open Data can be viewed by anyone
                            </label>
                        </div>

                        <hr>

                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong>  Make sure you entered everything correctly<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        {!! Form::open(array('method'=>'post','action'=>array('MetadataController@store',$pid,$fid))) !!}
                        {!! Form::token() !!}
                        {!! Form::hidden('type', 'addmetadata') !!}
                        <div class="form-group">
                            {!! Form::label('name','Linked Open Data name') !!}
                            {!! Form::text('name','',array('class'=>'form-control')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('field','Field to link with this Linked Open Data') !!}
                            {!! Form::select('field',$fields,'',array('class'=>'form-control')) !!}
                        </div>

                        {!! Form::submit('Assign Linked Open Data',array('class'=>'btn btn-primary form-control')) !!}

                        {!! Form::close() !!}
                        <hr>
                        <p><strong>Automatically Mass Assign Linked Open Data</strong></p>
                        <button id="massAssign" class="btn btn-primary form-control">Mass Assign Linked Open Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop

@section('footer')
    <script>

        $("#public_metadata").change(function(){
            var state = null;
            if($("#public_metadata").prop('checked')) state = true;
            else state = false;
            makeMetaPublic(state);
        });
        $("#massAssign").on('click',function(){
            massAssignMeta();
        });

        function deleteMeta(flid){
           var deleteURL ="{{action('MetadataController@destroy',compact('pid','fid'))}}";
            $.ajax({
                url:deleteURL,
                method:'DELETE',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "flid": flid
                },
                success: function(data){
                    location.reload();
                }
            });
        }
        function massAssignMeta(){
            var deleteURL ="{{action('MetadataController@massAssign',compact('pid','fid'))}}";
            $.ajax({
                url:deleteURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data){
                    location.reload();
                }
            });
        }


        function makeMetaPublic(state){
            var deleteURL ="{{action('MetadataController@store',compact('pid','fid'))}}";
            $.ajax({
                url:deleteURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "type":"visibility",
                    "state": state
                },
                success: function(data){
                    //location.reload();
                    console.log("Linked Open Data was changed");
                },
                error: function(jqxhr, textStatus, errorThrown){
                    console.log("Error in changing linked to open data visibility");
                    //console.log("text status: " + textStatus);
                    //console.log("error thrown: "+errorThrown);
                    alert("Sorry, there was an error when trying to change the Linked Open Data's visibility.");
                    location.reload()
                }
            });
        }
    </script>

@stop