<input type="hidden" name="userId" value="{{\Auth::user()->id}}">
<input type="hidden" name="tmpFileDir" value="{{$tmpFileDir}}">

@foreach($layout['pages'] as $page)
    <section id="#{{$page["title"]}}" class="page-section-js hidden">
        @foreach($page["flids"] as $flid)
            @php
                $field = $layout['fields'][$flid];
                $typedField = $form->getFieldModel($field['type']);
            @endphp
            
            @include($typedField->getFieldInputView(), ['flid' => $flid,'field' => $field, 'editRecord' => $editRecord])
        
            <div class="form-group mt-xs">
                <p class="sub-text">
                    {{$field['description']}}
                </p>
            </div>
        @endforeach
    </section>
@endforeach