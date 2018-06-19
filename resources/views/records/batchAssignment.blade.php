@extends('app', ['page_title' => 'Batch Assignment', 'page_class' => 'batch-assign'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'Batch Assign Field Values'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid, 'openDrawer' => true])
@stop

@section('stylesheets')
    <link rel="stylesheet" href="{{ config('app.url') }}assets/css/vendor/datetimepicker/jquery.datetimepicker.min.css" />
@stop

@section('header')
    <section class="head">
        <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-zap"></i>
                <span>Batch Assign Field Values</span>
            </h1>
            <p class="description">Brief info on what Batch Assign Field Values can do for the user, followed by
                instructions on how to mass assign records will go here.</p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")

    <form method="post" action="{{action('RecordController@massAssignRecords',compact('pid','fid'))}}">
        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <section class="record-batch center">
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
                <label for="searchable">Overwrite All Previously Inputted Fields?</label>
                <div class="check-box">
                    <input type="checkbox" value="1" id="preset" class="check-box-input" name="overwrite"/>
                    <div class="check-box-background"></div>
                    <span class="check"></span>
                    <span class="placeholder">Inputted fields will keep their value</span>
                    <span class="placeholder-alt">Inputted fields will be overwritten</span>
                </div>
            </div>

            <div class="form-group mt-xxxl">
                {!! Form::submit('Batch Assigned Field Values',['class' => 'btn disabled batch-submit-js']) !!}
            </div>
        </section>
    </form>
@stop

@section('footer')
    @include('partials.records.javascripts')

    <script src="{{ config('app.url') }}assets/javascripts/vendor/ckeditor/ckeditor.js"></script>

    <script>
        geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => 0]) }}';
        csrfToken = "{{ csrf_token() }}";

        Kora.Records.Batch();
    </script>
@stop
