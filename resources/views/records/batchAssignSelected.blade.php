@extends('app', ['page_title' => 'Batch Assignment', 'page_class' => 'batch-assign-selected'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'Batch Assign Field Value Records'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid, 'openDrawer' => true])
@stop

@section('stylesheets')

@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-zap"></i>
                <span>Batch Assign Field Value Records</span>
                <span class="heading-count mt-xs">(<span class="count"></span> Selected Records)</span>
            </h1>
            <p class="description">The Batch Assign Field Values page allows you to modify field values for the selected field across a number of records. You are currently set to Batch Assign Field Values for <span class="count"></span> Selected Records. To begin the Batch Assign process for these selected records, select a field below.</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")

    <form method="post" action="{{ action('RecordController@massAssignRecordSet',compact('pid','fid')) }}" class="batch-form">
        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <section class="record-batch-selected center">
            <div class="form-group">
                <label for="field_selection">Select the Field to Batch Assign</label>
                <select class="single-select field-to-batch-js" name="field_selection" data-placeholder="Search and Select a Field to Batch Assign">
                    <option value=""></option>
                    @foreach($fields as $field)
                        <option value="{{$field->flid}}">{{$field->name}}</option>
                    @endforeach
                </select>
            </div>

            @foreach($fields as $field)
                <section id="batch_{{$field->flid}}" class="batch-field-section-js hidden">
                    <?php $typedField = $field->getTypedField();  ?>
                    @include($typedField::FIELD_INPUT_VIEW, ['field' => $field, 'hasData' => false, 'editRecord' => false])
                    <div class="form-group mt-xs">
                        <p class="sub-text">
                            {{$field->desc}}
                        </p>
                    </div>
                </section>
            @endforeach

            <div class="form-group mt-xxxl">
                <div class="spacer"></div>
            </div>

            <div class="form-group mt-xxxl">
                {!! Form::submit('Batch Assigned Field Values',['class' => 'btn disabled batch-selected-submit-js']) !!}
            </div>
        </section>
    </form>
@stop

@section('footer')
    @include('partials.records.javascripts')

    <script src="{{ url('assets/javascripts/vendor/ckeditor/ckeditor.js') }}"></script>

    <script>
        geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => 0]) }}';
        csrfToken = "{{ csrf_token() }}";
        var validationUrl = "{{action('RecordController@validateMassRecord',['pid' => $form->pid, 'fid' => $form->fid])}}";

        Kora.Records.BatchSelected();
    </script>
@stop
