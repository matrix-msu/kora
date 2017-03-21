{!! Form::hidden('pid',$pid) !!}
<div class="form-group">
    {!! Form::label('name',trans('forms_form.name').': ') !!}
    {!! Form::text('name',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('slug',trans('forms_form.slug').': ') !!}
    {!! Form::text('slug',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('description',trans('forms_form.desc').': ') !!}
    {!! Form::textarea('description',null,['class' => 'form-control']) !!}
</div>

@if($submitButtonText == 'Create Form')

    <div class="form-group">
        {!! Form::label('admins',trans('forms_form.admin').'(s): ') !!}
        {!! Form::select('admins[]',$users, null,['class' => 'form-control', 'multiple', 'id' => 'admins']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('preset', trans('forms_form.preset').': ') !!}
        <select class="form-control" id="presets" name="preset">
            <option disabled selected>{{trans('forms_form.select')}}</option>
            @for($i=0; $i < sizeof($presets); $i++)
                <option value="{{$presets[$i]['fid']}}">{{$presets[$i]['name']}}</option>
            @endfor
        </select>
    </div>

@endif

<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>