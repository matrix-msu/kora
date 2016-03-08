<?php
//We need to clean up any lingering files in tmp for this form
$folder = 'f'.$field->flid.'u'.\Auth::user()->id;
$dir = env('BASE_PATH').'storage/app/tmpFiles/'.$folder;
if(file_exists($dir)) {
    //clear tmp and thumb folders
    foreach (new \DirectoryIterator($dir) as $file) {
        if ($file->isFile()) {
            unlink($dir.'/'.$file->getFilename());
        }
    }
    if(file_exists($dir.'/thumbnail')) {
        foreach (new \DirectoryIterator($dir.'/thumbnail') as $file) {
            if ($file->isFile()) {
                unlink($dir.'/thumbnail/'.$file->getFilename());
            }
        }
    }
    if(file_exists($dir.'/medium')) {
        foreach (new \DirectoryIterator($dir.'/medium') as $file) {
            if ($file->isFile()) {
                unlink($dir.'/medium/'.$file->getFilename());
            }
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
        <span>{{trans('records_fieldInput.addimg')}}...</span>
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
                var del = '<div id="uploaded_file_div">' + file.name + ' ';
                del += '<input type="hidden" name="file{{$field->flid}}[]" value ="'+file.name+'">';
                del += '<button id="up" class="btn btn-default" type="button">{{trans('records_fieldInput.up')}}</button>';
                del += '<button id="down"class="btn btn-default" type="button">{{trans('records_fieldInput.down')}}</button>';
                del += '<button class="btn btn-danger delete" type="button" data-type="' + file.deleteType + '" data-url="' + file.deleteUrl + '" >';
                del += '<i class="glyphicon glyphicon-trash" /> {{trans('records_fieldInput.delete')}}</button>';
                del += '</div>';

                $('#filenames{{$field->flid}}').append(del);
            });
        },
        fail: function (e,data){
            var error = data.jqXHR['responseText'];

            if(error=='InvalidType'){
                $('#file_error{{$field->flid}}').text('{{trans('records_fieldInput.invalid')}}.');
            } else if(error=='TooManyFiles'){
                $('#file_error{{$field->flid}}').text('{{trans('records_fieldInput.max')}} {{\App\Http\Controllers\FieldController::getFieldOption($field,'MaxFiles')}} {{trans('records_fieldInput.submit')}}.');
            } else if(error=='MaxSizeReached'){
                $('#file_error{{$field->flid}}').text('{{trans('records_fieldInput.exceed')}} {{\App\Http\Controllers\FieldController::getFieldOption($field,'FieldSize')}} kb');
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

    $('#filenames{{$field->flid}}').on('click','#up',function(){
        fileDiv = $(this).parent('#uploaded_file_div');

        if(fileDiv.prev('#uploaded_file_div').length==1){
            prevDiv = fileDiv.prev('#uploaded_file_div');

            fileDiv.insertBefore(prevDiv);
        }
    });

    $('#filenames{{$field->flid}}').on('click','#down',function(){
        fileDiv = $(this).parent('#uploaded_file_div');

        if(fileDiv.next('#uploaded_file_div').length==1){
            nextDiv = fileDiv.next('#uploaded_file_div');

            fileDiv.insertAfter(nextDiv);
        }
    });

    function numFiles(flid){
        var cnt = 0;
        $("#filenames"+flid).find(".button").each(function () {
            cnt++;
        });

        return cnt;
    }
</script>