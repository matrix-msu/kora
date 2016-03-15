<?php namespace App\Services;

/**
 * Helps with the dynamically generated text that needs to be translated at runtime.
 *
 * Class Translator
 * @package App\Services
 */
class Translator
{
    /**
     * Array of French HTML entities.
     */
    const FRENCH_HTML_ENTITIES = ['&agrave;', '&acirc;', '&ccedil;', '&eacute;', '&egrave;', '&ecirc;', '&euml;',
        '&icirc;', '&iuml;', '&oelig;', '&ocirc;', '&ugrave;', '&ucirc;', '&Agrave;', '&Acirc;', '&Ccedil;',
        '&Egrave;', '&Eacute;', '&Ecirc;', '&Euml;', '&Icirc;', '&Iuml;', '&OElig;', '&Ocirc;', '&Ugrave;',
        '&Ucirc;', '&laquo;', '&raquo;'];

    /**
     * Array of French unicode entities.
     */
    const FRENCH_UNICODE_ENTITIES = ['\u00e0', '\u00e2', '\u00e7', '\u00e9', '\u00e8', '\u00ea', '\u00eb',
        '\u00ee', '\u00ef', '\u0153', '\u00f4', '\u00f9', '\u00fb', '\u00c0', '\u00c2', '\u00c7',
        '\u00c8', '\u00c9', '\u00ca', '\u00cb', '\u00ce', '\u00cf', '\u0152', '\u00d4', '\u00d9',
        '\u00db', '\u00ab', '\u00bb'];

    /**
     * Array of Spanish HTML entities.
     */
    const SPANISH_HTML_ENTITIES = [];

    /**
     * Array of Spanish unicode entities.
     */
    const SPANISH_UNICODE_ENTITIES = [];

    /**
     * Converts a string that potentially has HTML entities to a string with unicode characters instead.
     * This is intended to print the lang strings in Javascript alert windows.
     *
     * @param $string, The string with HTML entities.
     */
    static public function html_entities_to_unicode($string) {

    }
}