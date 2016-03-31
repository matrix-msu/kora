<?php namespace App\Http\Controllers;

use App\DateField;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class OptionController extends Controller {

    public function getAdvancedOptionsPage(Request $request){
        $type = $request->type;
        if($type=="Text") {
            return view('partials.field_option_forms.text', compact('field', 'form', 'proj','presets'));
        }else if($type=="Rich Text") {
            return view('partials.field_option_forms.richtext', compact('field', 'form', 'proj'));
        }else if($type=="Number") {
            return view('partials.field_option_forms.number', compact('field', 'form', 'proj'));
        }else if($type=="List") {
            return view('partials.field_option_forms.list', compact('field', 'form', 'proj','presets'));
        }else if($type=="Multi-Select List") {
            return view('partials.field_option_forms.mslist', compact('field', 'form', 'proj','presets'));
        }else if($type=="Generated List") {
            return view('partials.field_option_forms.genlist', compact('field', 'form', 'proj','presets'));
        }else if($type=="Combo List") {
            return view('partials.field_option_forms.combolist', compact('field', 'form', 'proj'));
        }else if($type=="Date") {
            return view('partials.field_option_forms.date', compact('field', 'form', 'proj'));
        }else if($type=="Schedule") {
            return view('partials.field_option_forms.schedule', compact('field', 'form', 'proj','presets'));
        }else if($type=="Geolocator") {
            return view('partials.field_option_forms.geolocator', compact('field', 'form', 'proj','presets'));
        }else if($type=="Documents") {
            return view('partials.field_option_forms.documents', compact('field', 'form', 'proj'));
        }else if($type=="Gallery") {
            return view('partials.field_option_forms.gallery', compact('field', 'form', 'proj'));
        }else if($type=="Playlist") {
            return view('partials.field_option_forms.playlist', compact('field', 'form', 'proj'));
        }else if($type=="Video") {
            return view('partials.field_option_forms.video', compact('field', 'form', 'proj'));
        }else if($type=="3D-Model") {
            return view('partials.field_option_forms.3dmodel', compact('field', 'form', 'proj'));
        }else if($type=="Associator") {
            return view('partials.field_option_forms.associator', compact('field', 'form', 'proj'));
        }
    }

    public function updateAdvanced($field,Request $request){
        if($field->type=="Text") {
            return $this->updateText($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Rich Text") {
            return $this->updateRichtext($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Number") {
            return $this->updateNumber($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="List") {
            return $this->updateList($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Multi-Select List") {
            return $this->updateMultilist($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Generated List") {
            return $this->updateGenlist($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Combo List") {
            return $this->updateCombolist($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Date") {
            return $this->updateDate($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Schedule") {
            return $this->updateSchedule($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Geolocator") {
            return $this->updateGeolocator($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Documents") {
            return $this->updateDocument($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Gallery") {
            return $this->updateGallery($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Playlist") {
            return $this->updatePlaylist($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Video") {
            return $this->updateVideo($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="3D-Model") {
            return $this->updateModel($field->pid,$field->fid,$field->flid,$request,false);
        }else if($field->type=="Associator") {
            return $this->updateAssociator($field->pid,$field->fid,$field->flid,$request,false);
        }
    }

    public function updateModel($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $filetype = $request->filetype[0];
        for($i=1;$i<sizeof($request->filetype);$i++){
            $filetype .= '[!]'.$request->filetype[$i];
        }

        if($request->filesize==''){
            $request->filesize = 0;
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateOptions($pid, $fid, $flid, 'FieldSize', $request->filesize);
        FieldController::updateOptions($pid, $fid, $flid, 'FileTypes', $filetype);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateAssociator($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $reqDefs = $request->default;
        $def = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $def .= '[!]'.$reqDefs[$i];
        }

    }

    public function updateCombolist($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $flopt_one ='[Type]'.$request->typeone.'[Type][Name]'.$request->nameone.'[Name][Options]';

        if($request->typeone == 'Text'){
            $flopt_one .= '[!Regex!]'.$request->regex_one.'[!Regex!]';
            $flopt_one .= '[!MultiLine!]'.$request->multi_one.'[!MultiLine!]';
        }else if($request->typeone == 'Number'){
            $flopt_one .= '[!Max!]'.$request->max_one.'[!Max!]';
            $flopt_one .= '[!Min!]'.$request->min_one.'[!Min!]';
            $flopt_one .= '[!Increment!]'.$request->inc_one.'[!Increment!]';
            $flopt_one .= '[!Unit!]'.$request->unit_one.'[!Unit!]';
        }else if($request->typeone == 'List' | $request->typeone == 'Multi-Select List'){
            $flopt_one .= '[!Options!]';

            $reqOpts = $request->options_one;
            $options = $reqOpts[0];
            for($i=1;$i<sizeof($reqOpts);$i++){
                $options .= '[!]'.$reqOpts[$i];
            }
            $flopt_one .= $options;
            $flopt_one .= '[!Options!]';
        }else if($request->typeone == 'Generated List'){
            $flopt_one .= '[!Options!]';

            $reqOpts = $request->options_one;
            $options = $reqOpts[0];
            for($i=1;$i<sizeof($reqOpts);$i++){
                $options .= '[!]'.$reqOpts[$i];
            }
            $flopt_one .= $options;
            $flopt_one .= '[!Options!]';
            $flopt_one .= '[!Regex!]'.$request->regex_one.'[!Regex!]';
        }

        $flopt_one .= '[Options]';

        $flopt_two ='[Type]'.$request->typetwo.'[Type][Name]'.$request->nametwo.'[Name][Options]';

        if($request->typetwo == 'Text'){
            $flopt_two .= '[!Regex!]'.$request->regex_two.'[!Regex!]';
            $flopt_two .= '[!MultiLine!]'.$request->multi_two.'[!MultiLine!]';
        }else if($request->typetwo == 'Number'){
            $flopt_two .= '[!Max!]'.$request->max_two.'[!Max!]';
            $flopt_two .= '[!Min!]'.$request->min_two.'[!Min!]';
            $flopt_two .= '[!Increment!]'.$request->inc_two.'[!Increment!]';
            $flopt_two .= '[!Unit!]'.$request->unit_two.'[!Unit!]';
        }else if($request->typetwo == 'List' | $request->typetwo == 'Multi-Select List'){
            $flopt_two .= '[!Options!]';

            $reqOpts = $request->options_two;
            $options = $reqOpts[0];
            for($i=1;$i<sizeof($reqOpts);$i++){
                $options .= '[!]'.$reqOpts[$i];
            }
            $flopt_two .= $options;
            $flopt_two .= '[!Options!]';
        }else if($request->typetwo == 'Generated List'){
            $flopt_two .= '[!Options!]';

            $reqOpts = $request->options_two;
            $options = $reqOpts[0];
            for($i=1;$i<sizeof($reqOpts);$i++){
                $options .= '[!]'.$reqOpts[$i];
            }
            $flopt_two .= $options;
            $flopt_two .= '[!Options!]';
            $flopt_two .= '[!Regex!]'.$request->regex_two.'[!Regex!]';
        }

        $flopt_two .= '[Options]';

        $default='';
        if(!is_null($request->defvalone) && $request->defvalone != ''){
            $default .= '[!f1!]'.$request->defvalone[0].'[!f1!]';
            $default .= '[!f2!]'.$request->defvaltwo[0].'[!f2!]';

            for($i=1;$i<sizeof($request->defvalone);$i++){
                $default .= '[!def!]';
                $default .= '[!f1!]'.$request->defvalone[$i].'[!f1!]';
                $default .= '[!f2!]'.$request->defvaltwo[$i].'[!f2!]';
            }
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $default);
        FieldController::updateOptions($pid, $fid, $flid, 'Field1', $flopt_one);
        FieldController::updateOptions($pid, $fid, $flid, 'Field2', $flopt_two);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateDate($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        if(DateField::validateDate($request->default_month,$request->default_day,$request->default_year))
            $default = '[M]'.$request->default_month.'[M][D]'.$request->default_day.'[D][Y]'.$request->default_year.'[Y]';
        else{
            flash()->error(trans('controller_option.baddate'));

            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options')->withInput();
        }

        if($request->start==''){
            $request->start = 0;
        }
        if($request->end==''){
            $request->end = 9999;
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $default);
        FieldController::updateOptions($pid, $fid, $flid, 'Format', $request->format);
        FieldController::updateOptions($pid, $fid, $flid, 'Start', $request->start);
        FieldController::updateOptions($pid, $fid, $flid, 'End', $request->end);
        FieldController::updateOptions($pid, $fid, $flid, 'Circa', $request->circa);
        FieldController::updateOptions($pid, $fid, $flid, 'Era', $request->era);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateDocument($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $filetype = $request->filetype[0];
        for($i=1;$i<sizeof($request->filetype);$i++){
            $filetype .= '[!]'.$request->filetype[$i];
        }

        if($request->filesize==''){
            $request->filesize = 0;
        }
        if($request->maxfiles==''){
            $request->maxfiles = 0;
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateOptions($pid, $fid, $flid, 'FieldSize', $request->filesize);
        FieldController::updateOptions($pid, $fid, $flid, 'MaxFiles', $request->maxfiles);
        FieldController::updateOptions($pid, $fid, $flid, 'FileTypes', $filetype);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateGallery($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $filetype = $request->filetype[0];
        for($i=1;$i<sizeof($request->filetype);$i++){
            $filetype .= '[!]'.$request->filetype[$i];
        }

        if($request->filesize==''){
            $request->filesize = 0;
        }
        if($request->maxfiles==''){
            $request->maxfiles = 0;
        }

        $sx = $request->small_x;
        $sy = $request->small_y;
        if($sx=='')
            $sx = 150;
        if($sy=='')
            $sy = 150;
        $small = $sx.'x'.$sy;

        $lx = $request->large_x;
        $ly = $request->large_y;
        if($lx=='')
            $lx = 300;
        if($ly=='')
            $ly = 300;
        $large = $lx.'x'.$ly;

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateOptions($pid, $fid, $flid, 'FieldSize', $request->filesize);
        FieldController::updateOptions($pid, $fid, $flid, 'MaxFiles', $request->maxfiles);
        FieldController::updateOptions($pid, $fid, $flid, 'FileTypes', $filetype);
        FieldController::updateOptions($pid, $fid, $flid, 'ThumbSmall', $small);
        FieldController::updateOptions($pid, $fid, $flid, 'ThumbLarge', $large);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateGenlist($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        $reqOpts = $request->options;
        $options = $reqOpts[0];
        for($i=1;$i<sizeof($reqOpts);$i++){
            if ($request->regex!='' && !preg_match($request->regex, $reqOpts[$i]))
            {
                flash()->error(trans('controller_option.genregex',['opt' => $reqOpts[$i]]));

                return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options')->withInput();
            }
            $options .= '[!]'.$reqOpts[$i];
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $default);
        FieldController::updateOptions($pid, $fid, $flid, 'Regex', $request->regex);
        FieldController::updateOptions($pid, $fid, $flid, 'Options', $options);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateGeolocator($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $default);
        FieldController::updateOptions($pid, $fid, $flid, 'Map', $request->map);
        FieldController::updateOptions($pid, $fid, $flid, 'DataView', $request->view);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateList($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $reqOpts = $request->options;
        $options = $reqOpts[0];
        for($i=1;$i<sizeof($reqOpts);$i++){
            $options .= '[!]'.$reqOpts[$i];
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $request->default);
        FieldController::updateOptions($pid, $fid, $flid, 'Options', $options);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateMultilist($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        $reqOpts = $request->options;
        $options = $reqOpts[0];
        for($i=1;$i<sizeof($reqOpts);$i++){
            $options .= '[!]'.$reqOpts[$i];
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $default);
        FieldController::updateOptions($pid, $fid, $flid, 'Options', $options);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateNumber($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);
        //these are help prevent interruption of correct parameters when error is found in advanced setup
        $advString = '';

        if($request->min!='' && $request->max!=''){
            if($request->min >= $request->max){
                if($return){
                    flash()->error('The max value is less than or equal to the minimum value. ');

                    return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options')->withInput();
                }else{
                    $request->min = '';
                    $request->max = '';
                    $advString = 'The max value is less than or equal to the minimum value.';
                }
            }
        }

        if($request->default!='' && $request->max!=''){
            if($request->default > $request->max) {
                if($return){
                    flash()->error('The max value is less than the default value. ');

                    return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options')->withInput();
                }else{
                    $request->default = '';
                    $advString = 'The max value is less than the default value.';
                }
            }
        }

        if($request->default!='' && $request->min!=''){
            if($request->default < $request->min) {
                if($return){
                    flash()->error('The minimum value is greater than the default value. ');

                    return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options')->withInput();
                }else{
                    $request->default = '';
                    $advString = 'The minimum value is greater than the default value.';
                }
            }
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $request->default);
        FieldController::updateOptions($pid, $fid, $flid, 'Max', $request->max);
        FieldController::updateOptions($pid, $fid, $flid, 'Min', $request->min);
        FieldController::updateOptions($pid, $fid, $flid, 'Increment', $request->inc);
        FieldController::updateOptions($pid, $fid, $flid, 'Unit', $request->unit);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return $advString;
        }
    }

    public function updatePlaylist($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $filetype = $request->filetype[0];
        for($i=1;$i<sizeof($request->filetype);$i++){
            $filetype .= '[!]'.$request->filetype[$i];
        }

        if($request->filesize==''){
            $request->filesize = 0;
        }
        if($request->maxfiles==''){
            $request->maxfiles = 0;
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateOptions($pid, $fid, $flid, 'FieldSize', $request->filesize);
        FieldController::updateOptions($pid, $fid, $flid, 'MaxFiles', $request->maxfiles);
        FieldController::updateOptions($pid, $fid, $flid, 'FileTypes', $filetype);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateRichtext($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $request->default);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateSchedule($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        if($request->start==''){
            $request->start = 0;
        }
        if($request->end==''){
            $request->end = 9999;
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $default);
        FieldController::updateOptions($pid, $fid, $flid, 'Start', $request->start);
        FieldController::updateOptions($pid, $fid, $flid, 'End', $request->end);
        FieldController::updateOptions($pid, $fid, $flid, 'Calendar', $request->cal);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }

    public function updateText($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);
        $advString = '';

        if($request->regex!=''){
            $regArray = str_split($request->regex);
            if($regArray[0]!=end($regArray)){
                $request->regex = '/'.$request->regex.'/';
            }
            if ($request->default!='' && !preg_match($request->regex, $request->default))
            {
                if($return){
                    flash()->error('The default value does not match the given regex pattern.');

                    return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options')->withInput();
                }else{
                    $request->default = '';
                    $advString = 'The default value does not match the given regex pattern.';
                }
            }
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateDefault($pid, $fid, $flid, $request->default);
        FieldController::updateOptions($pid, $fid, $flid, 'Regex', $request->regex);
        FieldController::updateOptions($pid, $fid, $flid, 'MultiLine', $request->multi);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return $advString;
        }
    }

    public function updateVideo($pid, $fid, $flid, Request $request, $return=true){
        //dd($request);

        $filetype = $request->filetype[0];
        for($i=1;$i<sizeof($request->filetype);$i++){
            $filetype .= '[!]'.$request->filetype[$i];
        }

        if($request->filesize==''){
            $request->filesize = 0;
        }
        if($request->maxfiles==''){
            $request->maxfiles = 0;
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request->searchable);
        FieldController::updateOptions($pid, $fid, $flid, 'FieldSize', $request->filesize);
        FieldController::updateOptions($pid, $fid, $flid, 'MaxFiles', $request->maxfiles);
        FieldController::updateOptions($pid, $fid, $flid, 'FileTypes', $filetype);

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        }else{
            return '';
        }
    }
}
