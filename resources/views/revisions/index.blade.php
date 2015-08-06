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
                        <span><h3>{{$message}} Revision History</h3></span>
                        @if($message != 'Recent')
                            @if(App\Http\Controllers\RecordController::exists($rid))
                                <a href="{{action('RecordController@show', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $rid])}}">[Show Record]</a>
                            @else
                                [Record Deleted]
                            @endif
                        @endif
                    </div>

                    <div class="panel-body">

                        {!! Form::label('search', 'Search Record Revisions: ') !!}
                        {!! Form::select('search', $records, ['class'=>'form-control']) !!}
                        <button class="btn btn-primary" onclick="showRecordRevisions(1, '')">Show Record Revisions</button>
                        @if($message != 'Recent')
                        <button class="btn btn-primary" onclick="showRecordRevisions(-1, '')">Back to Recent Revisions</button>
                        @endif
                        <hr/>

                        @include('revisions.printrevisions')

                    </div>
                </div>
            </div>
        </div>
    </div>


@stop

@section('footer')
    <script>
    $('#search').select2({ width: 'hybrid'});

    function showRecordRevisions(flag, value) {
        if(flag==1){
            window.location.href = $('#search').val();
        }
        else if(flag==-1){
            window.location.href = 'recent';
        }
        else if(flag==0){
            window.location.href = value;
        }
    }

    function rollback(revision) {
        resp = confirm('Are you sure you want to roll this record back?');
        if(resp) {
            $.ajax({
              url: '{{action('RevisionController@rollback')}}',
              type: 'GET',
              data: {
                  "_token": "{{ csrf_token() }}",
                  "revision": revision
              },
              success: function(){
                  location.reload();
              }
            });
        }
    }

    $(".panel-heading").on("click", function(){
        if($(this).siblings('.collapseTest').css('display') == 'none') {
            $(this).siblings('.collapseTest').slideDown();
        } else {
            $(this).siblings('.collapseTest').slideUp();
        }
    });
    </script>
@stop