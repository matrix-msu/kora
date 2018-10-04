@foreach(explode('[!]',$typedField->documents) as $opt)
    <div class="record-data-card">
        <div class="field-display document-field-display">
            @if($opt != '')
                <?php
                $name = explode('[Name]', $opt)[1];
                $size = explode('[Size]', $opt)[1];
                $link = action('FieldAjaxController@getFileDownload',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name]);
                ?>
                <div>
                    <p class="mt-xs"><a class="documents-link underline-middle-hover" href="{{$link}}">{{$name}}</a></p>
                    <p class="">File size: {{$typedField->formatBytes($size)}}</p>
                </div>
            @endif
        </div>

        <div class="field-sidebar document-sidebar document-sidebar-js">
            <div class="top">
                <a class="field-btn external-button-js" target="_blank" href="{{url('projects/'.$form->pid.'/forms/'.$form->fid.'/records/'.$record->rid.'/fields/'.$field->flid.'/'.$name)}}">
                    <i class="icon icon-external-link"></i>
                </a>

                <a href="{{action('FieldAjaxController@getFileDownload', ['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name])}}"
                   class="field-btn">
                    <i class="icon icon-download"></i>
                </a>
            </div>
        </div>
    </div>
@endforeach







