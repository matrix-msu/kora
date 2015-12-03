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

<div class="form-group" id="field_types_div">
    {!! Form::label('type','Field Type: ') !!}
    {!! Form::select('type',
        ['Text Fields' => array('Text' => 'Text', 'Rich Text' => 'Rich Text', 'Number' => 'Number'),
        'List Fields' => array('List' => 'List', 'Multi-Select List' => 'Multi-Select List', 'Generated List' => 'Generated List',
            'Combo List' => 'Combo List'),
        'Date Fields' => array('Date' => 'Date', 'Schedule' => 'Schedule'),
        'File Fields' => array('Documents' => 'Documents','Gallery' => 'Gallery (jpg, gif, png)',
            'Playlist' => 'Playlist (mp3, wav, oga)', 'Video' => 'Video (mp4, ogv)', '3D-Model' => '3D-Model (obj, stl)'),
        'Specialty Fields' => array('Geolocator' => 'Geolocator (latlon, utm, textual)','Associator' => 'Associator')],
        null,['class' => 'form-control field_types']) !!}
    <div id="combo_field_types" style="display: none">
        {!! Form::label('cftype1','Combo Field Type 1: ') !!}
        {!! Form::select('cftype1',
            ['Text Fields' => array('Text' => 'Text', 'Number' => 'Number'),
            'List Fields' => array('List' => 'List', 'Multi-Select List' => 'Multi-Select List', 'Generated List' => 'Generated List')],
            null,['class' => 'form-control']) !!}
        {!! Form::label('cfname1','Combo Field Name 1: ') !!}
        {!! Form::text('cfname1',null,['class' => 'form-control']) !!}

        {!! Form::label('cftype2','Combo Field Type 2: ') !!}
        {!! Form::select('cftype2',
            ['Text Fields' => array('Text' => 'Text', 'Number' => 'Number'),
            'List Fields' => array('List' => 'List', 'Multi-Select List' => 'Multi-Select List', 'Generated List' => 'Generated List')],
            null,['class' => 'form-control']) !!}
        {!! Form::label('cfname2','Combo Field Name 2: ') !!}
        {!! Form::text('cfname2',null,['class' => 'form-control']) !!}
    </div>

    <script>
        $( document ).ready(function() {
            if($('.field_types').val()=='Combo List'){
                $('#combo_field_types').show();
            }
        });
        $('#field_types_div').on('change','.field_types', function(){
            if($('.field_types').val()=='Combo List'){
                $('#combo_field_types').show();
            }else{
                $('#combo_field_types').hide();
            }
        });
    </script>
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