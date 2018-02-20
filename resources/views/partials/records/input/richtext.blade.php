<div class="form-group mt-xl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    <textarea id="{{$field->flid}}" name="{{$field->flid}}" class="ckeditor-js"></textarea>
</div>