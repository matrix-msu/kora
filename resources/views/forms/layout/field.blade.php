<div class="field card " id="{{$field->flid}}">
  <div class="header {{ $index == 0 ? 'active' : '' }}">
    <div class="left">
      <div class="move-actions">
        <a class="action move-action-js up-js" href="">
          <i class="icon icon-arrow-up"></i>
        </a>

        <a class="action move-action-js down-js" href="">
          <i class="icon icon-arrow-down"></i>
        </a>
      </div>

      <a class="title underline-middle-hover" href="{{ action("FieldController@show",['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">
        <span class="name">{{$field->name}}</span>
        <i class="icon icon-arrow-right"></i>
      </a>
    </div>

    <div class="card-toggle-wrap">
      <a href="#" class="card-toggle project-toggle-js">
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
      <a class="quick-action delete-field delete-field-js left" href="#">
        <i class="icon icon-trash"></i>
      </a>

      <a class="quick-action underline-middle-hover" href="{{ action('FieldController@edit',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">
        <i class="icon icon-field"></i>
        <span>Edit Field</span>
      </a>

      <a class="quick-action underline-middle-hover" href="{{ action('FieldController@show',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">
        <span>View Field Options</span>
        <i class="icon icon-arrow-right"></i>
      </a>
    </div>
  </div>
</div>
















<div class="panel panel-default">
    <div class="panel-heading" style="font-size: 1.5em;">
        @if($field->type=='Associator' and sizeof(\App\Http\Controllers\AssociationController::getAvailableAssociations($field->fid))==0)
            <font color="red">{{$field->name}}</font>
        @elseif(\Auth::user()->canEditFields($form))
            <a href="{{ action('FieldController@show',['pid' => $field->pid,'fid' => $field->fid, 'flid' => $field->flid]) }}">{{ $field->name }}</a>
        @else
            {{$field->name}}
        @endif
        <span  class="pull-right">{{ App\Services\Translator::translate($field->type) }} </span>
    </div>
    <div class="collapseTest" style="">
        <div class="panel-body">
            <b>{{trans('projects_show.name')}}:</b> {{ $field->slug }}<br>
            <b>{{trans('forms_layout_printfield.desc')}}:</b> {{ $field->desc }}
        </div>
        <div class="panel-footer">

            @if(\Auth::user()->canEditFields($form))
                <span>
                    <a href="{{ action('FieldController@edit',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">[{{trans('forms_layout_printfield.edit')}}]</a>
                </span>
                <span>
                    <a href="{{ action('FieldController@show',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">[{{trans('forms_layout_printfield.options')}}]</a>
                </span>
            @endif
            @if(\Auth::user()->canDeleteFields($form))
                <span>
                    <a onclick="deleteField('{{ $field->name }}', {{ $field->flid }})" href="javascript:void(0)">[{{trans('forms_layout_printfield.delete')}}]</a>
                </span>
            @endif

            @if(\Auth::user()->canEditForms(\App\Http\Controllers\ProjectController::getProject($field->pid)))
                <span style="float:right">
                    <a onclick="moveField('{{ \App\Http\Controllers\PageController::_DOWN }}', {{ $field->flid }})" href="javascript:void(0)">[DOWN]</a>
                </span>
                <span style="float:right">
                    <a onclick="moveField('{{ \App\Http\Controllers\PageController::_UP }}', {{ $field->flid }})" href="javascript:void(0)">[UP]</a>
                </span>
            @endif
        </div>
    </div>
</div>
