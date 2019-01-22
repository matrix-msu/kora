<?php namespace App\Http\Requests;

class BlockRequest extends Request { //TODO::CASTLE

    /*
    |--------------------------------------------------------------------------
    | Block Request
    |--------------------------------------------------------------------------
    |
    | This request handles validation of request inputs for Dashboard Blocks
    |
    */

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'block_type' => 'required',
            'block_project' => 'required_if:block_type,Project',
            'block_form' => 'required_if:block_type,Form',
            'block_record' => 'required_if:block_type,Record',
            'block_note_title' => 'required_if:block_type,Note|max:40',
            'block_note_content' => 'required_if:block_type,Note',
            'section_to_add' => 'required'
        ];
    }
}
