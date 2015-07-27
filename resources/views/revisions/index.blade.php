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
                        <h3>{{$message}} Revision History</h3>
                    </div>

                    <div class="panel-body">
                        @foreach($revisions as $revision)
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                                    <span class="pull-right">{{ ucfirst($revision->type) }}</span>
                                </div>

                                <div class="collapseTest" style="display: none">
                                    <div class="panel-body">
                                        <div>Type: {{$revision->type}}</div>
                                        <div>Data: {{var_dump(json_decode($revision->data))}}</div>
                                    </div>
                                </div>
                            </div>


                        @endforeach

                    </div>



                </div>
            </div>
        </div>
    </div>


@stop

@section('footer')
    <script>
    $(".panel-heading").on("click", function(){
        if($(this).siblings('.collapseTest').css('display') == 'none') {
            $(this).siblings('.collapseTest').slideDown();
        } else {
            $(this).siblings('.collapseTest').slideUp();
        }
    });
    </script>
@stop