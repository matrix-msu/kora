<?php
  $fieldsInForm = \App\Field::where('fid','=',$fid)->get()->all();
  $cnt = sizeof($fieldsInForm);
?>

@if($cnt > 1)
  <li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ \App\Http\Controllers\FieldController::getField($flid)->name }}<b class="caret"></b></a>
    <ul class="dropdown-menu">
      <li class="dropdown-submenu" id="field-submenu"> <a href="#" data-toggle="dropdown">{{trans('partials_menu_options.jump')}}</a>
        <ul class="dropdown-menu">
          @foreach($fieldsInForm as $field)
            @if($field->flid != $flid)
              <li><a href="{{ url('/projects/'.$pid).'/forms/'.$field->fid .'/fields/'.$field->flid.'/options'}}">{{ $field->name }}</a></li>
            @endif
          @endforeach
        </ul>
      </li>
    </ul>
  </li>
@endif
