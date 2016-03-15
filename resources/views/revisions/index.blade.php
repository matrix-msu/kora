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
                        <span><h3>{{$message}} {{trans('revisions_index.history')}}</h3></span>
                        @if($message != 'Recent')
                            @if(App\Http\Controllers\RecordController::exists($rid))
                                <a href="{{action('RecordController@show', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $rid])}}">[{{trans('revisions_index.show')}}]</a>
                            @else
                                [{{trans('revisions_index.delete')}}]
                            @endif
                        @endif
                    </div>

                    <div class="panel-body">

                        {!! Form::label('search', trans('revisions_index.search').': ') !!}
                        {!! Form::select('search', $records, ['class'=>'form-control']) !!}
                        <button class="btn btn-primary" onclick="showRecordRevisions(1, '')">{{trans('revisions_index.showrev')}}</button>

                        @if($message != 'Recent')
                            <button class="btn btn-primary" onclick="showRecordRevisions(-1, '')">{{trans('revisions_index.back')}}</button>
                        @endif

                        <hr/>

                        <div id="revisions">
                            @include('revisions.printrevisions')
                        </div>

                        <div style="display:none; margin-top: 1em;" id="progress" class="progress">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
                                {{trans('update_index.loading')}}
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>


@stop

@section('footer')
    <script>
    $('#search').select2({ width: 'hybrid'});


    /**
     * Shows recent record revisions based on a simple flagging system.
     *
     * This function is a little silly but it gets the job done. The cases for flag are:
     *  1: The user wants to show the revisions for a particular record using the search box.
     * -1: The user wants to show the recent revision history for all records.
     *  0: The user wants to show the revisions for a certain record using a text link on the recent revision page.
     *
     * @param flag {int} The action that should be taken, listed above.
     * @param value {string} The KID (PID-FID-RID) of the record that we want to show (only used in case 0);
     */
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

    /**
     * Sends the ajax call to roll back a revision.
     * See RevisionController::rollback for more information.
     *
     * @param revision {int} The id of the revision.
     */
    function rollback(revision) {
        resp = confirm('{{trans('revisions_index.areyousure')}}?');
        if(resp) {

            showProgress();

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

    /**
     * Shows the progress bar.
     */
    function showProgress() {
        var revisions = $("#revisions");
        revisions.slideUp();

        var progress = $("#progress");
        progress.css("display", "");
    }

    /**
     * The collapsing display jQuery.
     */
    $( ".panel-heading" ).on( "click", function() {
        if ($(this).siblings('.collapseTest').css('display') == 'none' ){
            $(this).siblings('.collapseTest').slideDown();
        }else {
            $(this).siblings('.collapseTest').slideUp();
        }
    });
    </script>
@stop