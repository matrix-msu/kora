<div class="modal modal-js modal-mask new-permission-modal new-permission-modal-js">
  <div class="content overflow-yes">
    <div class="header">
      <span class="title">Create a New Form Association</span>
      <a href="#" class="modal-toggle modal-toggle-js">
        <i class="icon icon-cancel"></i>
      </a>
    </div>
    <div class="body">
      {!! Form::open(['method' => 'POST', 'action' => ['AssociationController@create', $form->project_id, $form->id]]) !!}
        <div class="form-group">
          {!! Form::label("new-form", "Select a Form to Allow Association") !!}
            <span class="error-message new-assoc-error-js"></span>
            <select class="single-select" id="new-form" name="assocfid"
              data-placeholder="Select a form here">
            <option></option>

            @php
      			$fids = array();
      			$sorted_associatable_forms = array();
      			foreach ($associatable_forms as $association) {
      				if (!in_array($association, $associatedForms)) {
      					$display_str = $association->project()->get()->first()->name . ' - ' . $association->name;

      					while (in_array($display_str, $sorted_associatable_forms)) {
      						$display_str = $display_str . ' ';
      					}

      					array_push($sorted_associatable_forms, $display_str);
      					$fids[$display_str] = $association->id;
      				}
      			}
      			sort($sorted_associatable_forms, SORT_FLAG_CASE | SORT_NATURAL);
      			@endphp

      			@foreach ($sorted_associatable_forms as $association_display_text) {
      				<option value="{{$fids[$association_display_text]}}">{{$association_display_text}}</option>
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
