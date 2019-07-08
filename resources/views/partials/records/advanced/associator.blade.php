<div class="form-group mt-xl">
    {!! Form::label($flid.'_input', array_key_exists('alt_name', $field) && $field['alt_name']!='' ? $field['name'].' ('.$field['alt_name'].')' : $field['name']) !!}
    <?php
        $asc = new \App\Http\Controllers\AssociatorSearchController();
        $request = new \Illuminate\Http\Request();
        $request->replace(['keyword' => '']);

        $results = $asc->assocSearch($form->project_id, $form->id, $flid, $request);
        $rids = array();

        foreach($results as $kid => $prevArray) {
            $preview = implode(" | ", $prevArray);
            $rids[$kid] = "$kid: $preview";
        }
    ?>
    {!! Form::select($flid . "_input[]", $rids, '', ["class" => "multi-select", "Multiple"]) !!}
</div>
