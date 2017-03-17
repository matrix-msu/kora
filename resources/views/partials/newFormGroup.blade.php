{!! Form::open(['method' => 'POST', 'action' => ['FormGroupController@create', 'pid'=>$form->pid, 'fid'=>$form->fid]]) !!}

<div class="form-group">
    {!! Form::label('name', trans('partials_newFormGroup.name').': ') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('users', trans('partials_newFormGroup.users').': ') !!}
    {!! Form::select('users[]', $users, null, ['id' => 'users', 'class' => 'form-control', 'multiple']) !!}
</div>

{!! Form::label('permissions', trans('partials_newFormGroup.permissions').': ') !!}<br/>

<div class="form-group" style="display: inline">
    {!! Form::label('create', trans('partials_newFormGroup.cField').': ') !!}
    {!! Form::checkbox('create', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group" style="display: inline">
    {!! Form::label('edit', trans('partials_newFormGroup.eField').': ') !!}
    {!! Form::checkbox('edit', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group" style="display: inline">
    {!! Form::label('delete', trans('partials_newFormGroup.dField').': ') !!}
    {!! Form::checkbox('delete', null, ['class' => 'form-control']) !!}
</div>

<br/>

<div class="form-group" style="display: inline">
    {!! Form::label('ingest', trans('partials_newFormGroup.cRec').': ') !!}
    {!! Form::checkbox('ingest', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group" style="display: inline">
    {!! Form::label('modify', trans('partials_newFormGroup.eRec').': ') !!}
    {!! Form::checkbox('modify', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group" style="display: inline">
    {!! Form::label('destroy', trans('partials_newFormGroup.dRec').': ') !!}
    {!! Form::checkbox('destroy', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::submit(trans('partials_newFormGroup.create'), ['class' => 'btn btn-primary form-control']) !!}
</div>

{!! Form::close() !!}