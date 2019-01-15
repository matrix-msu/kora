<input type="hidden" name="userId" value="{{\Auth::user()->id}}">

@foreach($layout as $page)
    <section id="#{{$page["title"]}}" class="page-section-js hidden">
        @foreach($page["fields"] as $flid => $field)
            @php
                $typedField = $form->getFieldModel($field['type']);
                if($editRecord)
                    $hasData = false; //TODO::CASTLE
                else
                    $hasData = false;
            @endphp
            
            @include($typedField->getFieldInputView(), ['flid' => $flid,'field' => $field, 'hasData' => $hasData, 'editRecord' => $editRecord])
        
            <div class="form-group mt-xs">
                <p class="sub-text">
                    {{$field['description']}}
                </p>
            </div>
        @endforeach
    </section>
@endforeach