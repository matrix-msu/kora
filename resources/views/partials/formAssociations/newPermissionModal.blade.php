<div class="modal modal-js modal-mask new-permission-modal new-permission-modal-js">
  <div class="content">
    <div class="header">
      <span class="title">Create a New Form Association</span>
      <a href="#" class="modal-toggle modal-toggle-js">
        <i class="icon icon-cancel"></i>
      </a>
    </div>
    <div class="body">
      {!! Form::open(['method' => 'POST', 'action' => ['AssociationController@create', $project->pid, $form->fid]]) !!}
        <div class="form-group">
          {!! Form::label("new-form", "Select a Form to Allow Association") !!}
          <select class="single-select" id="new-form" name="assocfid"
            data-placeholder="Select a form here">
            <option></option>
            @foreach ($associatable_forms as $association)
              @if (!in_array($association, $associatedForms))
                <option value="{{$association->fid}}">{{$association->project()->get()->first()->name}} - {{$association->name}}</option>
              @endif
            @endforeach
          </select>
        </div>

        <div class="form-group mt-xxl add-association-submit add-association-submit-js">
          {!! Form::submit('Create a New Form Association', ['class' => 'btn']) !!}
        </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>
