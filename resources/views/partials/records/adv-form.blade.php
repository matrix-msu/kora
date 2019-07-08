<form method="POST" class="adv-search-js" action="{{action("AdvancedSearchController@search", ["pid" => $form->project_id, "fid" => $form->id])}}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @foreach($form->layout['fields'] as $flid => $field)
        <?php $typedField = $form->getFieldModel($field['type']); ?>
        @if($field['advanced_search'])
            <input type="hidden" name="{{$flid}}" value="{{$flid}}">
            @if ($typedField->getAdvancedSearchInputView() != "")
              @include($typedField->getAdvancedSearchInputView(), ['flid' => $flid, 'field' => $field])
            @endif
            <div class="form-group mt-sm">
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$flid}}_negative" />
                    <span class="check"></span>
                    <span class="placeholder">Negative</span>
                    <span class="sub-text">(“Negative” Returns records that do not meet this search)</span>
                </div>
            </div>
            <div class="form-group mt-sm">
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$flid}}_empty" />
                    <span class="check"></span>
                    <span class="placeholder">Empty</span>
                    <span class="sub-text">(“Empty” Returns records that do not have a value for this field and behaves independent of the search field above)</span>
                </div>
            </div>
        @endif
    @endforeach
    <div class="form-group mt-xxxl">
        {!! Form::submit('Submit Advanced Search', ['class' => 'btn adv-search-js']) !!}
    </div>
</form>
