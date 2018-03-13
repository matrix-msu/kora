@extends('app', ['page_title' => "{$form->name} Form", 'page_class' => 'form-show'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
    <div class="inner-wrap center">
      <h1 class="title">
        <i class="icon icon-form"></i>
        <span>{{ $form->name }}</span>
        <a href="{{ action('FormController@edit',['pid' => $form->pid, 'fid' => $form->fid]) }}" class="head-button">
          <i class="icon icon-edit right"></i>
        </a>
      </h1>
      <p class="identifier">
        <span>Unique Form ID:</span>
        <span>{{ $form->slug }}</span>
      </p>
      <p class="description">{{ $form->description }}</p>

      <div class="form-group">
        <div class="form-quick-options">
          <div class="button-container">
            <a href="{{ url('/projects/'.$form->pid).'/forms/'.$form->fid.'/records'}}" class="btn half-sub-btn">View & Search Form Records</a>
            <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}" class="btn half-sub-btn">Create New Record</a>
          </div>
        </div>
      </div>
    </div>
  </section>
@stop


@section('body')
  <section class="filters center">
    <div class="underline-middle search search-js">
      <i class="icon icon-search"></i>
      <input type="text" placeholder="Find a Field">
      <i class="icon icon-cancel icon-cancel-js"></i>
    </div>
    <div class="show-options show-options-js">
      <a href="#" class="expand-fields-js" title="Expand all fields"><i class="icon icon-expand icon-expand-js"></i></a>
      <a href="#" class="collapse-fields-js" title="Collapse all fields"><i class="icon icon-condense icon-condense-js"></i></a>
    </div>
  </section>

  <div class="modal modal-js modal-mask page-delete-modal-js">
    <div class="content small">
      <div class="header">
        <span class="title">Delete Page?</span>
        <a href="#" class="modal-toggle modal-toggle-js">
          <i class="icon icon-cancel"></i>
        </a>
      </div>
      <div class="body">
        <span class="description">
          Are you sure you wish to delete this page from this form?
          Doing so will also delete all of the fields within this page.
          You must move all fields to a different form page if you wish
          to keep them.
        </span>

        <div class="form-group">
          <a href="#" class="btn warning delete-page-confirm-js">Delete Page</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-js modal-mask field-delete-modal-js">
    <div class="content small">
      <div class="header">
        <span class="title">Delete Field?</span>
        <a href="#" class="modal-toggle modal-toggle-js">
          <i class="icon icon-cancel"></i>
        </a>
      </div>
      <div class="body">
        <span class="description">
          Are you sure you wish to delete this field from this page?
        </span>

        <div class="form-group">
          <a href="#" class="btn warning delete-field-confirm-js">Delete Field</a>
        </div>
      </div>
    </div>
  </div>

  <section class="pages pages-js center">
    @foreach($pageLayout as $idx=>$page)
      <div class="page" page-id="{{$page["id"]}}">
        <div class="page-header">
          <div class="move-actions">
            <a class="action move-action-page-js up-js" page_id="{{$page["id"]}}" href="#">
              <i class="icon icon-arrow-up"></i>
            </a>

            <a class="action move-action-page-js down-js" page_id="{{$page["id"]}}" href="#">
              <i class="icon icon-arrow-down"></i>
            </a>
          </div>

          <div class="form-group title-container">
            {!! Form::text('name', null, ['class' => 'title page-title-js', 'placeholder' => $page["title"], 'pageid' => $page["id"]]) !!}
          </div>

          <div>
            <a href="#" data-page="{{$page["id"]}}" class="cancel-container delete-page-js">
              <i class="icon icon-cancel"></i>
            </a>
          </div>
        </div>

        <div class="field-sort-js" style="min-height: 10px;">
        @foreach($page["fields"] as $index=>$field)
            <div class="field-container">
              @include('forms.layout.field', ['field' => $field])
            </div>
          @endforeach
        </div>

        @if(\Auth::user()->canCreateFields($form))
          <form method="DET" action="{{action('FieldController@create', ['pid' => $form->pid, 'fid' => $form->fid, 'rootPage' => $page['id']]) }}">
            <div class="form-group new-field-button">
              <input type="submit" value="Create New Field Here">
            </div>
          </form>
        @endif
      </div>

      @if(\Auth::user()->canCreateFields($form))
        <div class="form-group new-page-button">
          <a href="#" data-new-page="{{$idx + 2}}" data-prev-page="{{$page["id"]}}" class="new-page-js btn transparent">Create New Form Page Here</a>
        </div>
      @endif

    @endforeach
  </section>
@stop

@section('javascripts')
  @include('partials.forms.javascripts')

  <script type="text/javascript">
    var modifyFormPageRoute = "{{ action('PageController@modifyFormPage', ['pid' => $form->pid, 'fid' => $form->fid]) }}";
    var addMethod = "{{\App\Http\Controllers\PageController::_ADD}}";
    var delMethod = "{{\App\Http\Controllers\PageController::_DELETE}}";
    var renameMethod = "{{\App\Http\Controllers\PageController::_RENAME}}";
    var upMethod = "{{\App\Http\Controllers\PageController::_UP}}";
    var downMethod = "{{\App\Http\Controllers\PageController::_DOWN}}";
    Kora.Forms.Show();
  </script>
@stop

@section('content')
    <div>
        <a href="{{ action('RecordController@index',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.records')}}]</a>
        @if(\Auth::user()->canIngestRecords($form))
            <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.newrec')}}]</a>
            <a href="{{ action('RecordController@importRecordsView',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.import')}}]</a>
        @endif
        @if(\Auth::user()->canModifyRecords($form))
            <a href="{{ action('RecordController@showMassAssignmentView',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.massassign')}}]</a>
        @endif
    </div>

    <hr/>

    @include('search.bar', ['pid' => $form->pid, 'fid' => $form->fid])


    @if (\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
        <hr/>

        <h4>{{trans('forms_show.formpanel')}}</h4>
        <form action="{{action('FormGroupController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">{{trans('forms_show.mGroups')}}</button>
        </form>
        <form action="{{action('AssociationController@index', ['fid'=>$form->fid, 'pid'=>$form->pid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">{{trans('forms_show.mAssoc')}}</button>
        </form>
        <form action="{{action('RevisionController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">{{trans('forms_show.mRecRiv')}}</button>
        </form>
        <form action="{{action('RecordPresetController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">{{trans('forms_show.mRecPre')}}</button>
        </form>
        <div>
            <span>{{trans('forms_show.makepreset')}}: </span><input type="checkbox" onchange="presetForm()" id="preset" @if($form->preset) checked @endif>
        </div>
        <form method="post" action="{{action('RecordController@createTest', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <input type="hidden" value="{{ csrf_token() }}" name="_token">
            <div><b>Create Test Records ({{trans('records_create.max')}} 1000):</b></div>
            <input type="number" name="test_records_num" value="1" step="1" max="1000" min="1">
            <button type="submit" class="btn btn-default">Create</button>
        </form>
    @endif
    <hr/>
    <h2>{{trans('forms_show.fields')}}</h2>

    @if(\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
        <div>
            <a href="{{ action('ExportController@exportForm',['pid' => $form->pid, 'fid' => $form->fid]) }}">[{{trans('forms_show.export')}}]</a>
        </div> <br>
    @endif

@stop

@section('footers')
    <script>
        $( ".panel-heading" ).on( "click", function() {
            if ($(this).siblings('.collapseTest').css('display') == 'none' ){
                $(this).siblings('.collapseTest').slideDown();
            }else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

        $( "#form_pages" ).on( "click", ".move_pageUp", function() {
            var page_id = $(this).attr('pageid');

            $.ajax({
                //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('PageController@modifyFormPage', ['pid' => $form->pid, 'fid' => $form->fid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "method": "{{\App\Http\Controllers\PageController::_UP}}",
                    "pageID": page_id
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        $( "#form_pages" ).on( "click", ".move_pageDown", function() {
            var page_id = $(this).attr('pageid');

            $.ajax({
                //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
                //We pass in a blank project to the action array and then manually add the id
                url: '{{ action('PageController@modifyFormPage', ['pid' => $form->pid, 'fid' => $form->fid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "method": "{{\App\Http\Controllers\PageController::_DOWN}}",
                    "pageID": page_id
                },
                success: function (result) {
                    location.reload();
                }
            });
        });

        function deleteField(fieldName, flid) {
            var encode = $('<div/>').html("{{ trans('forms_show.areyousure') }} ").text();
            var response = confirm(encode + fieldName + "?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because our JS isn't aware of the fid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('FieldController@destroy', ['pid' => $form->pid, 'fid' => $form->fid, 'flid' => '']) }}/'+flid,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (result) {
                        location.reload();
                    }
                });
            }
        }

        function moveField(direction, flid){
            var move_url = '{{ action('PageController@moveField', ['pid' => $form->pid, 'fid' => $form->fid, 'flid' => 'FLID']) }}';
            move_url = move_url.replace("FLID",flid);
            $.ajax({
                url: move_url,
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "direction": direction
                },
                success: function (result) {
                    console.log(result);
                    if(result=="success"){
                        location.reload();
                    }
                }
            });
        }

        function presetForm(){
            var preset;
            if($('#preset').is(':checked'))
                preset = 1;
            else
                preset = 0;
            $.ajax({
                url: '{{action('FormController@preset', ['pid' => $form->pid, 'fid' => $form->fid])}}',
                type: 'POST',
                data: {
                    "_token": '{{csrf_token()}}',
                    "preset": preset
                }
            });
        }
    </script>
@stop
