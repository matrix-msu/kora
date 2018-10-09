<div class="form-group mt-xxxl">
    {!! Form::label('options_'.$fnum, 'List Options') !!}
	<div class="container list-options-container-js">
		<p class="description-text">Add List Options below, and order them via drag & drop or their arrow icons.</p>
		<div class="ui-sortable list-options-js">
			@foreach(\App\ComboListField::getComboList($field,false,$fnum) as $opt)
				<div class="card ui-sortable-handle">
					<div class="header">
						<div class="left">
							<div class="move-actions">
								<a class="action move-action-js up-js">
									<i class="icon icon-arrow-up"></i>
								</a>
								<a class="action move-action-js down-js">
									<i class="icon icon-arrow-down"></i>
								</a>
							</div>
							<span class="title">{{$opt}}</span>
						</div>
						<div class="card-toggle-wrap">
							<a class="quick-action delete-option delete-option-js tooltip" tooltip="Delete Option">
								<i class="icon icon-trash"></i>
							</a>
						</div>
					</div>
				</div>
			@endforeach
		</div>
		<div class="input-section">
			<input type="text" class="add-options add-list-option-js" placeholder='Type here and hit the enter key or "Add" to add new list options'>
			<div class="submit">Add</div>
		</div>
	</div>
<!--
    <select multiple class="multi-select modify-select list-options-js" name="options_{{$fnum}}[]" data-placeholder="Select or Add Some Options">
        @foreach(\App\ComboListField::getComboList($field,false,$fnum) as $opt)
            <option value="{{$opt}}">{{$opt}}</option>
        @endforeach
    </select>
-->
</div>

<div class="form-group mt-xxxl">
	<div class="spacer"></div>
</div>
