@foreach(explode('[!]',$typedField->documents) as $opt)
    @if($opt != '')
        <?php
        $name = explode('[Name]',$opt)[1];
        $link = action('FieldAjaxController@getFileDownload',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name]);
        ?>
        <div><a class="documents-link underline-middle-hover" href="{{$link}}">{{$name}}</a></div>
    @endif
@endforeach