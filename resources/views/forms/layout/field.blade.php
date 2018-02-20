<div class="field card {{ $index == 0 ? 'active' : '' }}" id="{{$field->flid}}">
  <div class="header {{ $index == 0 ? 'active' : '' }}">
    <div class="left">
      @if(\Auth::user()->canEditForms(\App\Http\Controllers\ProjectController::getProject($field->pid)))
        <div class="move-actions">
          <a class="action move-action-js up-js" href="">
            <i class="icon icon-arrow-up"></i>
          </a>

          <a class="action move-action-js down-js" href="">
            <i class="icon icon-arrow-down"></i>
          </a>
        </div>
      @endif

      @if($field->type=='Associator' and sizeof(\App\Http\Controllers\AssociationController::getAvailableAssociations($field->fid))==0)
        <font color="red">{{$field->name}}</font>
      @elseif(\Auth::user()->canEditFields($form))
        <a class="title underline-middle-hover" href="{{ action('FieldController@show',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">
          <span class="name">{{$field->name}}</span>
          <i class="icon icon-arrow-right"></i>
        </a>
      @else
        <a class="title inactive underline-middle-hover" href="#">
          <span class="name">{{$field->name}}</span>
        </a>
      @endif
    </div>

    <div class="card-toggle-wrap">
      <a href="#" class="card-toggle field-toggle-js">
        <span class="chevron-text">{{$field->type}}</span>
        <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
      </a>
    </div>
  </div>

  <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
    <div class="id">
      <span class="attribute">Nick Name: </span>
      <span>{{$field->slug}}</span>
    </div>

    <div class="description">
      {{$field->desc}}
    </div>

    <div class="footer">
      @if(\Auth::user()->canDeleteFields($form))
        <a class="quick-action delete-field delete-field-js left" href="#">
          <i class="icon icon-trash"></i>
        </a>
      @endif

      @if(\Auth::user()->canEditFields($form))
        <a class="quick-action underline-middle-hover" href="{{ action('FieldController@show',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">
          <span>View Field Options</span>
          <i class="icon icon-arrow-right"></i>
        </a>
      @endif
    </div>
  </div>
</div>
