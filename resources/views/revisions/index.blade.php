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
                            <?php $data = unserialize($revision->data) ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                                        <span class="pull-right">{{ ucfirst($revision->type) }}</span>
                                    </div>

                                    <div class="collapseTest" style="display: none">
                                        <div>Revision Type: {{$revision->type}}</div>
                                        <div>Rollback: @if($revision->rollback)True @else False @endif</div>
                                        @if($revision->rollback)
                                            <a href="javascript:void(0)" onclick="rollback({{$revision->id}})">[Rollback Record]</a>
                                        @endif
                                        <div class="panel panel-default">
                                            <div>
                                                <b>Record: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                                            </div>
                                            @foreach($form->fields()->get() as $field)
                                                <div>
                                                    <span><b>{{$field->name}}:</b></span>
                                                        <span>
                                                            @if($field->type=='Text')
                                                                {{$data['textfields'][$field->flid]['data']}}
                                                            @elseif($field->type=='Rich Text')
                                                                <?php echo $data['richtextfields'][$field->flid]['data']; ?>
                                                            @elseif($field->type=='Number')
                                                                <?php
                                                                echo $data['numberfields'][$field->flid]['data'];
                                                                if($data['numberfields'][$field->flid]['data'] != '')
                                                                    echo ' '.App\Http\Controllers\FieldController::getFieldOption($field,'Unit');
                                                                ?>
                                                            @elseif($field->type=='List')
                                                                {{$data['listfields'][$field->flid]['data']}}
                                                            @elseif($field->type=='Multi-Select List')
                                                                @foreach(explode('[!]', $data['multiselectlistfields'][$field->flid]['data']) as $opt )
                                                                    <div>{{$opt}}</div>
                                                                @endforeach
                                                            @elseif($field->type=='Generated List')
                                                                @foreach(explode('[!]', $data['generatedlistfields'][$field->flid]['data']) as $opt)
                                                                    <div>{{$opt}}</div>
                                                                @endforeach
                                                            @endif
                                                        </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                        @endforeach

                        {!! Form::label('search', 'Search Record Revisions: ') !!}
                        {!! Form::select('search', $records, ['class'=>'form-control', 'id'=>'search']) !!}
                        <button class="btn btn-primary" onclick="showRecordRevisions()">Show Record Revisions</button>

                    </div>
                </div>
            </div>
        </div>
    </div>


@stop

@section('footer')
    <script>
    $('#search').select2();

    function showRecordRevisions() {
        var rid = $('#search').val();
        window.location.href = rid;
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