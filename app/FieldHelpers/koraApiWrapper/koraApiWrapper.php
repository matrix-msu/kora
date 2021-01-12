<?php

class koraApiWrapper {

    /*
    |--------------------------------------------------------------------------
    | kora Api Wrapper
    |--------------------------------------------------------------------------
    |
    | Generate and execute a kora API call for api features, including Project
    | and Form functions, as well as Record Search/Create/Edit/Delete
    |
    | The goal is to simplify making API calls, while also handling everything in
    | native PHP so that the developer does not have to interact with JSON
    |
    */

    /**
     * @var string - URL of the kora installations's API
     */
    private $koraApiURL; //"http://www.myKoraInstall.com/api/"

    /**
     * @var string - Standard output formats
     */
    const JSON = "JSON";
    const KORA_OLD = "KORA_OLD";
    const XML = "XML";

    /**
     * @var array - Valid output formats
     */
    const VALID_FORMATS = [ self::JSON, self::KORA_OLD, self::XML];

    /**
     * Constructs the class
     */
    public function __construct($url) {
        $this->koraApiURL = $url;
    }

    /**
     * Gets the version number of the kora installation.
     *
     * @return bool|string - API Result
     */
    public function getKoraVersion() {
        return $this->callAPI("version");
    }

    /**
     * Gets a list of forms belonging to the given project, including their Form ID, name, and description.
     *
     * @param  int $pid - Project ID
     * @return bool|string - API Result
     */
    public function getProjectForms($pid) {
        return $this->callAPI("projects/$pid/forms");
    }

    /**
     * Gets a list of fields belonging to the given form, including their configurations.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return bool|string - API Result
     */
    public function getFormFields($pid, $fid) {
        return $this->callAPI("projects/$pid/forms/$fid/fields");
    }

    /**
     * Gets the specific form layout dump.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return bool|string - API Result
     */
    public function getFormLayout($pid, $fid) {
        return $this->callAPI("projects/$pid/forms/$fid/layout");
    }

    /**
     * Gets the number of records belonging to a given form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return bool|string - API Result
     */
    public function getFormRecordCount($pid, $fid) {
        return $this->callAPI("projects/$pid/forms/$fid/recordCount");
    }

    /**
     * Creates a form in a project using the JSON structure required of kora's import form feature.
     *
     * @param  string $token - Kora Token w/ create permissions
     * @param  int $pid - Project ID
     * @param  string $form - JSON string of Form structure (matches Form import) //TODO::NO JSON
     * @return bool|string - API Result
     */
    public function createForm($token, $pid, $form) {
        $data = ['bearer_token' => $token, 'form' => $form];
        return $this->callAPI("projects/$pid/forms/create", "POST", $data);
    }

    /**
     * Edit field specific options of a field. NOTE: This can not edit general field configurations.
     *
     * @param  string $token - Kora Token w/ edit permissions
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  string $fields - JSON string of Field options to modify //TODO::NO JSON
     * @return bool|string - API Result
     */
    public function editFieldOptions($token, $pid, $fid, $fields) {
        $data = ['bearer_token' => $token, 'fields' => $fields];
        return $this->callAPI("projects/$pid/forms/$fid/fields", "POST", $data);
    }

    /**
     * Create a new record in kora.
     *
     * @param  string $token - Kora Token w/ create permissions
     * @param  int $fid - Form ID
     * @param  string $fields - JSON string representing the record (matches Record JSON import) //TODO::NO JSON
     * @param  string $zip - Path of zip file containing record files
     * @return bool|string - API Result
     */
    public function createRecord($token, $fid, $fields, $zip = null) {
        $data = ['bearer_token' => $token, 'form' => $fid, 'fields' => $fields];
        return $this->callAPI("create", "POST", $data, $zip);
    }

    /**
     * Edit a record in kora.
     *
     * @param  string $token - Kora Token w/ edit permissions
     * @param  int $fid - Form ID
     * @param  string $kid - Kora ID
     * @param  string $fields - JSON string representing the record (matches Record JSON import) //TODO::NO JSON
     * @param  string $zip - Path of zip file containing record files
     * @return bool|string - API Result
     */
    public function editRecord($token, $fid, $kid, $fields, $zip = null) {
        $data = ['_method' => 'put', 'bearer_token' => $token, 'form' => $fid, 'kid' => $kid, 'fields' => $fields];
        return $this->callAPI("edit", "POST", $data, $zip);
    }

    /**
     * Delete record(s) in kora.
     *
     * @param  string $token - Kora Token w/ delete permissions
     * @param  int $fid - Form ID
     * @param  array $kids - Array of kora IDs for records to be deleted
     * @return bool|string - API Result
     */
    public function deleteRecords($token, $fid, $kids) {
        $data = ['_method' => 'delete', 'bearer_token' => $token, 'form' => $fid, 'kids' => json_encode($kids)];
        return $this->callAPI("delete", "POST", $data);
    }

    /**
     * Search for records within a kora form.
     *
     * @param  koraFormSearch $form - The form search to execute
     * @param  string $format - Format of the returned records
     * @return bool|string - API Result
     */
    public function formRecordSearch($form, $format = self::JSON) {
        if(!in_array($format,self::VALID_FORMATS))
            $format = self::JSON;

        $forms = [$form->generateFormArray()];
        $data = ['forms' => json_encode($forms), 'format' => $format];

        return $this->callAPI("search", "POST", $data);
    }

    /**
     * Search for records within multiple kora forms.
     * NOTE: Results will be returned in separate arrays for each form.
     * NOTE: If global search features are used, results will be combined into one array.
     *
     * @param  array $forms - The koraFormSearch objects to execute
     * @param  string $format - Format of the returned records
     * @param  array $merge - Definitions for the global merged field name that will represent specific fields within each form search (global search feature)
     *      NOTE: If a particular field group is not defined, fields with the same name in each form will auto combine. Non matching fields will be appended on extra
     *      i.e. Example for 3 form searches (field names must be in same order as $forms array)
     * [
     *      "Title" => ["title", "Title", "Name"],
     *      "Description" => ["Notes", "Notes", "Notes"]
     * ]
     * @param  array $sort - Similar to sort structure for koraFormSearch, but allows use of $merge fields (global search feature)
     *      NOTE: In this example, when paired with $merge above, Author is assumed to be a field in all the forms
     * [
     *     ["Description" => "ASC"],
     *     ["Author" => "DESC"],
     *     ["created_at" => "ASC"],
     *     ...
     * ]
     * @param  int $index - Integer value that represents the index the search result set will start (global search feature)
     * @param  int $count - Integer value that represents the number of records returned  (global search feature)
     * @return bool|string - API Result
     */
    public function multiFormRecordSearch($forms, $format = self::JSON, $merge = null, $sort = null, $index = null, $count = null) {
        if(!in_array($format,self::VALID_FORMATS))
            $format = self::JSON;

        $generated = [];
        foreach($forms as $form) {
            $generated[] = $form->generateFormArray();
        }

        $data = ['forms' => json_encode($generated), 'format' => $format];

        !is_null($merge) ? $data['merge'] = json_encode($merge): null;
        !is_null($sort) ? $data['sort'] = json_encode($sort): null;
        !is_null($index) ? $data['index'] = $index: null;
        !is_null($count) ? $data['count'] = $count: null;

        return $this->callAPI("search", "POST", $data);
    }

    /**
     * Makes request to the api and returns results
     *
     * @param  string $function - KIDs we are searching for
     * @param  string $method - Defines if it is a GET/POST/PUT/DELETE call
     * @param  array $data - For non GET calls, contains any data that needs to be passed to the api
     * @param  string $zip - For non GET calls, path of zip file to upload to request
     * @return bool|string - If successful, returns the string result of the api call //TODO::JSON result fix
     */
    private function callAPI($function, $method = "GET", $data = null, $zip = null) {
        if($method=="POST" && file_exists($zip)) {
            $file = new CURLFile($zip, 'application/zip', 'zip_file');
            if(isset($file)) {
                $data['zip_file'] = $file;
            }
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->koraApiURL.$function);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($method=="POST") {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if(!$result = curl_exec($curl))
            return curl_error($curl);

        curl_close($curl);

        //Determine if result is a JSON object, so we can determine if it needs to be decoded before returned
        $jsonResult = json_decode($result, true);
        if($jsonResult === null)
            return $result;
        else
            return $jsonResult;
    }
}

?>
