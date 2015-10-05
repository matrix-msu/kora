<?php
//We need to clean up any lingering files in tmp for this form
$folder = 'f'.$field->flid.'u'.\Auth::user()->id;
$dir = env('BASE_PATH').'storage/app/tmpFiles/'.$folder;
if(file_exists($dir)) {
    foreach (new \DirectoryIterator($dir) as $file) {
        if ($file->isFile()) {
            unlink($dir.'/'.$file->getFilename());
        }
    }
}
?>
<div class="form-group" id="fileDiv{{$field->flid}}">
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    <span class="btn btn-success fileinput-button">
        <span>Add video...</span>
        <input id="file{{$field->flid}}" type="file" name="file{{$field->flid}}[]"
               data-url="{{ env('BASE_URL') }}public/saveTmpFile/{{$field->flid}}" multiple>
        {!! Form::hidden($field->flid,'f'.$field->flid.'u'.\Auth::user()->id) !!}
    </span>
    <br/><br/>
    <div id="progress">
        <div class="bar{{$field->flid}} progress-bar" style="width: 0%; height:18px; background:green;"></div>
    </div>
    <br/>
    <div id="file_error{{$field->flid}}" style="color: red"></div>
    <div id="filenames{{$field->flid}}"></div>
</div>

<script>
    $('#file{{$field->flid}}').fileupload({
        dataType: 'json',
        singleFileUploads: false,
        done: function (e, data) {
            $('#file_error{{$field->flid}}').text('');
            $.each(data.result['file{{$field->flid}}'], function (index, file) {
                var del = '<div>' + file.name + ' ';
                del += '<button class="btn btn-danger delete" type="button" data-type="' + file.deleteType + '" data-url="' + file.deleteUrl + '" >';
                del += '<i class="glyphicon glyphicon-trash" /> DELETE</button>';
                del += '</div>';

                $('#filenames{{$field->flid}}').append(del);
            });
        },
        fail: function (e,data){
            var error = data.jqXHR['responseText'];

            if(error=='InvalidType'){
                $('#file_error{{$field->flid}}').text('One or more submitted files has an invalid file type.');
            } else if(error=='TooManyFiles'){
                $('#file_error{{$field->flid}}').text('A maximum of {{\App\Http\Controllers\FieldController::getFieldOption($field,'MaxFiles')}} file(s) can be submitted.');
            } else if(error=='MaxSizeReached'){
                $('#file_error{{$field->flid}}').text('Adding the selected file(s) would exceed the max file limit of {{\App\Http\Controllers\FieldController::getFieldOption($field,'FieldSize')}} kb');
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar{{$field->flid}}').css(
                    'width',
                    progress + '%'
            );
        }
    });
    $('#filenames{{$field->flid}}').on('click','.delete',function(){
        var div = $(this).parent();
        $.ajax({
            url: $(this).attr('data-url'),
            type: 'DELETE',
            dataType: 'json',
            data: {
                "_token": '{{csrf_token()}}'
            },
            success: function (data) {
                div.remove();
            }
        });
    });

    function numFiles(flid){
        var cnt = 0;
        $("#filenames"+flid).find(".button").each(function () {
            cnt++;
        });

        return cnt;
    }
</script>