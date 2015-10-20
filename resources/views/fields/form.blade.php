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
    {!! Form::label('type','Field Type: ') !!}
    {!! Form::select('type',
        ['Text Fields' => array('Text' => 'Text', 'Rich Text' => 'Rich Text', 'Number' => 'Number'),
        'List Fields' => array('List' => 'List', 'Multi-Select List' => 'Multi-Select List', 'Generated List' => 'Generated List'),
        'Date Fields' => array('Date' => 'Date', 'Schedule' => 'Schedule'),
        'File Fields' => array('Documents' => 'Documents','Gallery' => 'Gallery (jpg, gif, png)',
            'Playlist' => 'Playlist (mp3, wav, oga)', 'Video' => 'Video (mp4, ogv)', '3D-Model' => '3D-Model (obj, stl)'),
        'Specialty Fields' => array('Geolocator' => 'Geolocator (latlon, utm, textual)','Associator' => 'Associator')],
        null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('desc','Description: ') !!}
    {!! Form::textarea('desc',null,['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('required','Required: ') !!}
    {!! Form::select('required',['false', 'true'], 'false', ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>