@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>{{trans('association_index.manageassoc')}}</h3>
                    </div>
                    <div class="panel-body">
                        <div id="form_select">{{trans('association_index.forms')}}:
                            <select id="selected_assoc">
                                @foreach(\App\Form::all() as $f)
                                    @if(!in_array($f->fid,$associds) && $f->fid != $form->fid)
                                        <?php
                                        $p = \App\Http\Controllers\ProjectController::getProject($f->pid);
                                        ?>
                                        <option value="{{$f->fid}}">{{$p->name}} | {{$f->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button id="add_assoc" class="btn btn-primary">{{trans('association_index.addallowed')}}</button>
                        </div>
                        <br>
                        <div id="form_allowed">
                            <b>{{trans('association_index.allowedassoc')}}</b>
                            <hr>
                            @foreach($assocs as $a)
                                <?php
                                    $f = \App\Form::where('fid','=',$a->assocForm)->first();
                                ?>
                                <p id="form_assoc_listitem">{{$f->name}} <a class="delete_assoc" fid="{{$f->fid}}" href="javascript:void(0)">[X]</a></p>
                            @endforeach
                        </div>
                        <div>
                            <br>
                            <b>{{trans('association_index.formsassoc')}}</b>
                            <hr>
                            @foreach(\App\Http\Controllers\AssociationController::getAvailableAssociations($form->fid) as $a)
                                <?php
                                $f = \App\Form::where('fid','=',$a->dataForm)->first();
                                $p = \App\Http\Controllers\ProjectController::getProject($f->pid);
                                ?>
                                <p id="assoc_to_listitem">{{$p->name}} | {{$f->name}}</p>
                            @endforeach
                        </div>
                        {!! Form::open(['action'=>['AssociationController@requestAccess',$form->pid,$form->fid]]) !!}
                        <div id="request_select">{{trans('association_index.requestfull')}}:
                            <select id="req_avail_assoc" name="rfid">
                                @foreach(\App\Http\Controllers\AssociationController::getRequestableAssociations($form->fid) as $f)
                                    <?php
                                    $p = \App\Http\Controllers\ProjectController::getProject($f->pid);
                                    ?>
                                    <option value="{{$f->fid}}">{{$p->name}} | {{$f->name}}</option>
                                @endforeach
                            </select>
                            <button id="request_assoc" class="btn btn-primary">{{trans('association_index.request')}}</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>
        $('#form_select').on('click','#add_assoc', function(){
            var assocfid = $('#selected_assoc').val();

            $.ajax({
                //Same method as deleteProject
                url: '{{ action('AssociationController@create',['pid'=>$form->pid, 'fid'=>$form->fid])}}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "assocfid": assocfid
                },
                success: function(){
                    var name = $('option:selected', '#selected_assoc').text();
                    $('option:selected', '#selected_assoc').remove();
                    $('#form_allowed').append("<p id='form_assoc_listitem'>"+name+" <a class='delete_assoc' fid='"+assocfid+"' href='javascript:void(0)'>[X]</a></p>");
                }
            });
        });

        $('#form_allowed').on('click','.delete_assoc', function(){
            var assocfid = $(this).attr('fid');
            var listitem = $(this).parent();
            var namelink = listitem.text();
            var name = namelink.split(" [X]")[0];

            $.ajax({
                //Same method as deleteProject
                url: '{{ action('AssociationController@destroy',['pid'=>$form->pid, 'fid'=>$form->fid])}}',
                type: 'DELETE',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "assocfid": assocfid
                },
                success: function(){
                    listitem.remove();
                    html = "<option value='"+assocfid+"'>"+name+"</option>";
                    curr = $('#selected_assoc').html();
                    $('#selected_assoc').html(curr+html);
                }
            });
        });
    </script>
@stop