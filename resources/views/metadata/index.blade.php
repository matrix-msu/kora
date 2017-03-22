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

                        <span><h3>{{trans('metadata_index.lod')}}</h3></span>
                        <hr>

                        <!-- Go To Metadata -->
                        <p>{{ trans("metadata_index.click") }} <a target="_blank" href="{{ url("/projects/" . $form->pid . "/forms/" . $form->fid . "/metadata/public") }}">{{ trans("metadata_index.here") }}</a> {{ trans("metadata_index.to_view") }}</p>

                        <hr/>

                        <!-- Activate Metadata -->
                        <div class="checkbox">
                            <label>
                                @if ($form->public_metadata == true)
                                    <input id="public_metadata" name="public_metadata" type="checkbox" value="true" checked>
                                @else
                                    <input id="public_metadata" name="public_metadata" type="checkbox" value="true">
                                @endif
                                {{trans('metadata_index.viewable')}}
                            </label>
                        </div>

                        <hr>

                        <!-- Resource Title -->
                        {!! Form::open(array('method'=>'post','action'=>array('MetadataController@updateResource',$pid,$fid))) !!}
                        {!! Form::token() !!}
                        <div class="form-group">
                            {!! Form::label('title','Resource Title') !!}
                            {!! Form::text('title',$resource_title,array('class'=>'form-control')) !!}
                        </div>
                        {!! Form::submit('Update Resource Title',array('class'=>'btn btn-primary form-control')) !!}
                        {!! Form::close() !!}

                        <hr/>

                        <!-- Field to Metadata Pairs -->
                        <div>
                            <b class="pull-left">{{trans('metadata_index.field')}}</b>
                            <b class="pull-right">{{trans('metadata_index.lod')}}</b>
                        </div>
                        <br>

                        <div id="field_content">
                            @foreach($assigned_fields as $f)
                                <hr style="margin-top: 10px;margin-bottom: 10px">
                                <div>
                                    <div>
                                        @if($f->metadata()->first()->primary)
                                            <b>{{ $f->name }}</b> (Primary Index)
                                        @elseif($f->type=="Text")
                                            {{ $f->name }} <input type="button" flid="{{$f->flid}}" class="btn-primary index_select" style="height:25px;" value="Make Primary">
                                        @else
                                            {{ $f->name }}
                                        @endif
                                        <a href="#"  onclick="deleteMeta({{$f->flid}})" class="pull-right">[X]</a>
                                        <span style="padding-right:5px"  class="pull-right">{{$f->metadata()->first()->name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <!-- Set Field Metadata Pairs -->
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>{{trans('metadata_index.whoops')}}!</strong>  {{trans('metadata_index.makesure')}}<br><br>
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
                            {!! Form::label('name',trans('metadata_index.name')) !!}
                            {!! Form::text('name','',array('class'=>'form-control')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('field',trans('metadata_index.linkedfield')) !!}
                            {!! Form::select('field',$fields,'',array('class'=>'form-control')) !!}
                        </div>

                        <?php $disabled = (!!count($fields)) ? "" : "disabled"; // If there are no fields the assign LOD button is disabled. ?>

                        {!! Form::submit(trans('metadata_index.assign'),array('class'=>'btn btn-primary form-control', $disabled)) !!}
                        {!! Form::close() !!}

                        <hr>

                        <!-- Auto-assign Metadata Pairs -->
                        <p><strong>{{trans('metadata_index.automass')}}</strong></p>
                        <button id="massAssign" class="btn btn-primary form-control">{{trans('metadata_index.massassign')}}</button>
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

        $('.index_select').on("click", function() {
            var flid = $(this).attr('flid');
            makePrimary(flid);
        });

        $("#assign").on("click", function() {
           loading();
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

        function makePrimary(flid){
            var primaryURL ="{{action('MetadataController@makePrimary',compact('pid','fid'))}}";
            $.ajax({
                url:primaryURL,
                method:'POST',
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
            $("#field_content").slideToggle(600, function() {
                $('#loading').slideToggle(400);
            });

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
                    var encode = $('<div/>').html("{{ trans('metadata_index.error') }}").text();
                    alert(encode + ".");
                    location.reload()
                }
            });
        }
    </script>

@stop