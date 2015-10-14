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
                        <h3>Manage Form Associations</h3>
                    </div>
                    <div class="panel-body">
                        <div id="form_select">Forms:
                            <select id="selected_assoc">
                                @foreach(\App\Form::all() as $f)
                                    @if(!in_array($f->fid,$associds) && $f->fid != $form->fid)
                                        <option value="{{$f->fid}}">{{$f->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button id="add_assoc" class="btn btn-primary">Add Allowed Association</button>
                        </div>
                        <br>
                        <div id="form_allowed">
                            <b>Allowed Associations by this Form</b>
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
                            <b>Forms that this Form Associates</b>
                            <hr>
                            @foreach(\App\Http\Controllers\AssociationController::getAvailableAssociations($form->fid) as $a)
                                <?php
                                $f = \App\Form::where('fid','=',$a->dataForm)->first();
                                ?>
                                <p id="assoc_to_listitem">{{$f->name}}</p>
                            @endforeach
                        </div>
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
                }
            });
        });
    </script>
@stop