@foreach(explode('[!]',$typedField->video) as $key => $vid)
    @if($vid != '')
        <div class="record-data-card">
          <div class="field-display video-field-display video-field-display-js">
            <video height="300" width="auto" controls>
              <?php $filename = explode('[Name]',$vid)[1]; ?>
              <source data-filename="{{explode('[Name]',$vid)[1]}}" src="{{url('app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.explode('[Name]',$vid)[1])}}" type="{{explode('[Type]',$vid)[1]}}">

              Your browser does not support the video tag.
            </video>
          </div>

          <div class="field-sidebar video-sidebar video-sidebar-js">
              <div class="top">
                  <a href="{{url('projects/'.$field->pid.'/forms/'.$field->fid.'/records/'.$record->rid.'/fields/'.$field->flid.'/'.$filename)}}" class="field-btn" target="_blank">
                      <i class="icon icon-external-link"></i>
                  </a>

                  <a href="{{ action('FieldAjaxController@getFileDownload', ['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $filename]) }}" class="field-btn">
                      <i class="icon icon-download"></i>
                  </a>
              </div>

              <div class="bottom">
                  <div class="field-btn full-screen-button-js">
                      <i class="icon icon-maximize"></i>
                  </div>
              </div>
          </div>
        </div>
    @endif
@endforeach
