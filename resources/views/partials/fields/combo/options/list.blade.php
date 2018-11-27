<div class="form-group specialty-field-group list-input-form-group list-input-form-group-combo mt-xxxl">
	{!! Form::label('options_{{$fnum}}','List Options') !!}

	<div class="form-input-container">
		<p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

		<!-- Cards of list options -->
		<div class="list-option-card-container list-option-card-container-{{$fnum}}-js">
			@foreach(\App\ComboListField::getComboList($field,false,$fnum) as $option)
				<div class="card list-option-card list-option-card-js" data-list-value="{{ $option }}">
					<input type="hidden" class="list-option-js" name="options_{{$fnum}}[]" value="{{ $option }}">

					<div class="header">
						<div class="left">
							<div class="move-actions">
								<a class="action move-action-js up-js" href="">
									<i class="icon icon-arrow-up"></i>
								</a>

								<a class="action move-action-js down-js" href="">
									<i class="icon icon-arrow-down"></i>
								</a>
							</div>

							<span class="title">{{ $option }}</span>
						</div>

						<div class="card-toggle-wrap">
							<a class="list-option-delete list-option-delete-js tooltip" href="" tooltip="Delete List Option"><i class="icon icon-trash"></i></a>
						</div>
					</div>
				</div>
			@endforeach
		</div>

		<!-- Card to add list options -->
		<div class="card new-list-option-card new-list-option-card-{{$fnum}}-js">
			<div class="header">
				<div class="left">
					<input class="new-list-option new-list-option-{{$fnum}}-js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options'>
				</div>

				<div class="card-toggle-wrap">
					<a class="list-option-add list-option-add-{{$fnum}}-js" href=""><span>Add</span></a>
				</div>
			</div>
		</div>
	</div>

    <div><a href="#" class="field-preset-link open-regex-modal-js">Use a Value Preset for these List Options</a></div>
</div>
