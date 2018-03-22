@if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==1)
    <?php echo nl2br($typedField->text) ?>
@else
    {{ $typedField->text }}
@endif