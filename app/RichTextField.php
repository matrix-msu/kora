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
        $text = $this->rawtext;
        $text = strip_tags($text); // We don't care to search the HTML tags and the user probably doesn't want to either.
        $text = html_entity_decode($text); // Some entities get encoded, some don't! PHP! What a language! So we'll just decode the ones that did.

        return self::keywordRoutine($args, $partial, $text);
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
}