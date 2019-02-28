@extends('app', ['page_title' => 'Batch Assignment', 'page_class' => 'batch-assign'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.static', ['name' => 'Batch Assign Field Values'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
@stop

@section('stylesheets')

@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-zap"></i>
                <span>Batch Assign Field Values</span>
            </h1>
            <p class="description">The Batch Assign Field Values page allows you to modify field values for the selected field across all records. To begin the Batch Assign process, select a field below. You can also Batch Assign Field Values to only a selection of records. To begin this process, head to the View Form Records page. For more information on Batch Assigning Field Values, refer to the <a href="#">Batch Assign Field Values - Kora Documentation.</a></p>
        </div>
    </section>
@stop

@section('body')
    @include("partials.fields.input-modals")

    <form method="post" action="{{action('RecordController@massAssignRecords',compact('pid','fid'))}}" class="batch-form">
        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <section class="record-batch center">
            <div class="form-group">
                <label for="field_selection">Select the Field to Batch Assign</label>
                <select class="single-select field-to-batch-js" name="field_selection" data-placeholder="Search and Select a Field to Batch Assign">
                    <option value=""></option>
                    @foreach($fields as $flid => $field)
                        <option value="{{$flid}}">{{$field['name']}}</option>
                    @endforeach
                </select>
            </div>

            @foreach($fields as $flid => $field)
                <section id="batch_{{$flid}}" class="batch-field-section-js hidden">
                    @php $typedField = $form->getFieldModel($field['type']); @endphp
                    @include($typedField->getFieldInputView(), ['field' => $field, 'hasData' => false, 'editRecord' => false])
                    <div class="form-group mt-xs">
                        <p class="sub-text">
                            {{$field['description']}}
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

    <script src="{{ url('assets/javascripts/vendor/ckeditor/ckeditor.js') }}"></script>

    <script>
        geoConvertUrl = '{{ action('FieldAjaxController@geoConvert',['pid' => $form->project_id, 'fid' => $form->id, 'flid' => 0]) }}';
        csrfToken = "{{ csrf_token() }}";
        var validationUrl = "{{action('RecordController@validateMassRecord',['pid' => $form->project_id, 'fid' => $form->id])}}";

        Kora.Records.Batch();
    </script>
@stop
