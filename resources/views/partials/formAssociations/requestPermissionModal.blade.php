<div class="modal modal-js modal-mask request-permission-modal request-permission-modal-js">
  <div class="content">
    <div class="header">
      <span class="title">Request Form Association</span>
      <a href="#" class="modal-toggle modal-toggle-js">
        <i class="icon icon-cancel"></i>
      </a>
    </div>
    <div class="body">
      {!! Form::open(['method' => 'POST', 'action' => ['AssociationController@create', $form->project_id, $form->id]]) !!}
        <div class="form-group">
          {!! Form::label("request-form", "Select a Form to Request Association") !!}
            <span class="error-message request-assoc-error-js"></span>
          <select class="single-select" id="request-form" name="assocfid"
            data-placeholder="Select a form here">
            <option></option>
            @foreach ($requestable_associations as $association)
              <option value="{{$association->id}}">{{$association->project()->get()->first()->name}} - {{$association->name}}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group mt-xxl request-assopciation-submit request-association-submit-js">
          {!! Form::submit('Request Form Association', ['class' => 'btn']) !!}
        </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>
