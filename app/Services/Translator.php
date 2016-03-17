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
     * Array of HTML entities.
     */
    const HTML_CHAR_ENTITIES = ['&agrave;', '&acirc;', '&ccedil;', '&eacute;', '&egrave;', '&ecirc;', '&euml;',
        '&icirc;', '&iuml;', '&oelig;', '&ocirc;', '&ugrave;', '&ucirc;', '&Agrave;', '&Acirc;', '&Ccedil;',
        '&Egrave;', '&Eacute;', '&Ecirc;', '&Euml;', '&Icirc;', '&Iuml;', '&OElig;', '&Ocirc;', '&Ugrave;',
        '&Ucirc;', '&laquo;', '&raquo;', '&aacute;', '&iacute;', '&ntilde;', '&oacute;', '&uacute;', '&uuml;',
        '&Aacute;', '&Iacute;', '&Ntilde;', '&Oacute;', '&Uacute;', '&Uuml;', '&iquest;', '&iexcl;'];

    /**
     * Array of unicode entities.
     */
    const UNICODE_CHAR = ['\u00e0', '\u00e2', '\u00e7', '\u00e9', '\u00e8', '\u00ea', '\u00eb',
        '\u00ee', '\u00ef', '\u0153', '\u00f4', '\u00f9', '\u00fb', '\u00c0', '\u00c2', '\u00c7',
        '\u00c8', '\u00c9', '\u00ca', '\u00cb', '\u00ce', '\u00cf', '\u0152', '\u00d4', '\u00d9',
        '\u00db', '\u00ab', '\u00bb', '\u00e1', '\u00ed', '\u00f1', '\u00f3', '\u00fa', '\u00fc',
        '\u00c1', '\u00cd', '\u00d1', '\u00d3', '\u00da', '\u00dc', '\u00bf', '\u00a1'];


    /**
     * Converts a string that potentially has HTML entities to a string with unicode characters instead.
     * This is intended to print the lang strings in Javascript alert windows.
     *
     * @param $string, The string with HTML entities.
     * @return string, The string with unicode entities.
     */
    static public function html_entities_to_unicode($string) {
        return str_replace(self::HTML_CHAR_ENTITIES, self::UNICODE_CHAR, $string);
    }
}