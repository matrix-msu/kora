<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RichTextField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'rawtext',
        'searchable_rawtext'
    ];

    /**
     * Keyword search for a rich text field.
     *
     * @param array $args, Array of arguments for the search to use.
     * @param bool $partial, True if partial values should be considered in the search.
     * @return bool, True if the search parameters are satisfied.
     */
    public function keywordSearch(array $args, $partial)
    {
        return self::keywordRoutine($args, $partial, $this->searchable_rawtext);
    }

    /**
     * Saves the model.
     *
     * Instead of putting this everywhere the rawtext member is assigned we'll just override the member function.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = array()) {
        $this->searchable_rawtext = strip_tags($this->rawtext);

        return parent::save($options);
    }

    /**
     * Determine if to metadata can be called on this field.
     *
     * @return bool
     */
    public function isMetafiable() {
        return ! empty($this->rawtext);
    }

    /**
     * Simply returns the rawtext.
     *
     * @param Field $field, unneeded.
     * @return string
     */
    public function toMetadata(Field $field) {
        return $this->rawtext;
    }
}