<div class="record-data-card">
  <div class="field-display richtext-field-display richtext-field-display-js">
    <div class="richtext richtext-js">
      <p>{!! $typedField->rawtext !!}</p>
      <div class="show-more-richtext show-more-richtext-js" showing="less">Show All</div>
    </div>
  </div>

  {{--<div class="field-sidebar richtext-sidebar richtext-sidebar-js">--}}
      {{--<div class="top">--}}
          {{--<a href="{{url('projects/'.$form->pid.'/forms/'.$form->fid.'/records/'.$record->rid.'/fields/'.$field->flid.'/richtext')}}" class="field-btn" target="_blank">--}}
              {{--<i class="icon icon-external-link"></i>--}}
          {{--</a>--}}
      {{--</div>--}}

      {{--<div class="bottom">--}}
          {{--<div class="field-btn full-screen-button-js">--}}
              {{--<i class="icon icon-maximize"></i>--}}
          {{--</div>--}}
      {{--</div>--}}
  {{--</div>--}}

  {{--<div class="full-screen-modal modal modal-js modal-mask richtext-map-modal richtext-map-modal-js">--}}
      {{--<div class="content">--}}
          {{--<div class="header">--}}
              {{--<span class="title">{{$field->name}}</span>--}}

              {{--<a href="#" class="modal-toggle modal-toggle-js">--}}
                  {{--<i class="icon icon-cancel"></i>--}}
              {{--</a>--}}
          {{--</div>--}}

          {{--<div class="body">--}}
              {{--<p>{!! $typedField->rawtext !!}</p>--}}
          {{--</div>--}}
      {{--</div>--}}
  {{--</div>--}}
</div>
