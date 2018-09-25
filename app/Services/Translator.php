<?php namespace App\Services;

use Illuminate\Support\Facades\Auth;

class Translator {

    /*
    |--------------------------------------------------------------------------
    | Translator
    |--------------------------------------------------------------------------
    |
    | This service handles dynamically generated text that needs to be translated at runtime
    |
    */

    /**
     * @var array - English translations of dynamically generated words
     */
    const ENGLISH_TRANS = [];
    /**
     * @var array - Spanish translations of dynamically generated words
     */
    const SPANISH_TRANS = [];
    /**
     * @var array - French translations of dynamically generated words
     */
    const FRENCH_TRANS = [];
    /**
     * @var array - Constant associative array of the above translations
     */
    const TRANS = ['en' => self::ENGLISH_TRANS, 'es' => self::SPANISH_TRANS, 'fr' => self::FRENCH_TRANS];

    /**
     * There are some constant text values that are dynamically generated text values that must be translated after the
     * fact, so we do that here using the above arrays.
     *
     * @param  string $string - The string to be translated
     * @return string - The translated string
     */
    public static function translate($string) {
        $lang = Auth::user()->language;
        return str_replace(self::TRANS['en'], self::TRANS[$lang], $string);
    }
}