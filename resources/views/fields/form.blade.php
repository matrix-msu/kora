{!! Form::hidden('pid',$pid) !!}
{!! Form::hidden('fid',$fid) !!}
<div class="form-group">
    {!! Form::label('name','Name: ') !!}
    {!! Form::text('name',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('slug','Internal Reference Name (no spaces, alpha-numeric values only): ') !!}
    {!! Form::text('slug',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('order','Order: ') !!}
    {!! Form::text('order',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('type','Field Type: ') !!}
    {!! Form::select('type', ['text', 'list', 'geolocator'], null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('description','Description: ') !!}
    {!! Form::textarea('description',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('required','Required: ') !!}
    {!! Form::select('required',['false', 'true'], 'false', ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('default','Default Value: ') !!}
    {!! Form::text('default',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>