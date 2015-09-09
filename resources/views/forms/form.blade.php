{!! Form::hidden('pid',$pid) !!}
<div class="form-group">
    {!! Form::label('name','Name: ') !!}
    {!! Form::text('name',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('slug','Internal Reference Name (no spaces, alpha-numeric values only): ') !!}
    {!! Form::text('slug',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('description','Description: ') !!}
    {!! Form::textarea('description',null,['class' => 'form-control']) !!}
</div>

@if($submitButtonText == 'Create Form')

    <div class="form-group">
        {!! Form::label('admins','Form Admin(s): ') !!}
        {!! Form::select('admins[]',$users, null,['class' => 'form-control', 'multiple', 'id' => 'admins']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('preset', 'Preset: ') !!}
        <select class="form-control" id="presets" name="preset">
            <option disabled selected>Select a Preset</option>
            @for($i=0; $i < sizeof($presets); $i++)
                <option value="{{$presets[$i]['fid']}}">{{$presets[$i]['name']}}</option>
            @endfor
        </select>
    </div>

@endif

<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>