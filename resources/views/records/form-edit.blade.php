@foreach(\App\Http\Controllers\PageController::getFormLayout($form->fid) as $page)
    <h3>{{$page["title"]}}</h3>
    <hr>
    @foreach($page["fields"] as $field)
        @include('records.layout.editfield', ['field' => $field])
    @endforeach
    <hr>
@endforeach
<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>