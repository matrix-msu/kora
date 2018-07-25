<div class="form-group mt-xl">
    {!! Form::label($field->flid.'_input',$field->name) !!}
    <?php
        $asc = new \App\Http\Controllers\AssociatorSearchController();
        $request = new \Illuminate\Http\Request();
        $request->replace(['keyword' => '']);

        $results = $asc->assocSearch($field->pid,$field->fid,$field->flid,$request);
        $rids = array();
        foreach($results as $kid => $prevArray) {
            $preview = implode(" | ", $prevArray);
            $rids[$kid] = "$kid: $preview";
        }
    ?>
    {!! Form::select($field->flid . "_input[]", $rids, '', ["class" => "multi-select", "Multiple"]) !!}
</div>