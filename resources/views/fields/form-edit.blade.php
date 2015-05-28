{!! Form::hidden('pid',$pid) !!}
{!! Form::hidden('fid',$fid) !!}
{!! Form::hidden('type',$type) !!}
{!! Form::hidden('required',$required) !!}
<div class="form-group">
    {!! Form::label('name','Name: ') !!}
    {!! Form::text('name',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('slug','Internal Reference Name (no spaces, alpha-numeric values only): ') !!}
    {!! Form::text('slug',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('desc','Description: ') !!}
    {!! Form::textarea('desc',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>