{!! Form::hidden('pid',$pid) !!}
{!! Form::hidden('fid',$fid) !!}
{!! Form::hidden('type',$type) !!}
{!! Form::hidden('required',$required) !!}
<div class="form-group">
    {!! Form::label('name',trans('fields_form-edit.name').': ') !!}
    {!! Form::text('name',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('slug',trans('fields_form-edit.slug').': ') !!}
    {!! Form::text('slug',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('desc',trans('fields_form-edit.desc').': ') !!}
    {!! Form::textarea('desc',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>