<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldValuePreset extends Model {

    /*
    |--------------------------------------------------------------------------
    | Field Value Preset
    |--------------------------------------------------------------------------
    |
    | This model represents a field value preset for use in a field
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ['project_id', 'preset', 'shared'];

    protected $casts = [
        'preset' => 'array',
    ];

    /**
     * @var array - Which fields can access which FVP
     */
    public static $compatability = [
        Form::_TEXT => ['Regex'],
        Form::_LIST => ['List'],
        Form::_MULTI_SELECT_LIST => ['List'],
        Form::_GENERATED_LIST => ['List','Regex']
    ];

    /**
     * @var array - Stock field value presets for various field types.
     */
    public static $STOCKPRESETS = [
        ["name" => "URL_URI","type"=>"Regex","preset"=>"/^(http|ftp|https):\/\//"],
        ["name" => "Boolean","type"=>"List","preset"=>["True","False"]],
        ["name" => "Countries","type"=>"List","preset"=>["United States","United Nations","Canada","Mexico","Afghanistan","Albania","Algeria","American Samoa","Andorra","Angola","Anguilla","Antarctica","Antigua and Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bosnia and Herzegowina","Botswana","Bouvet Island","Brazil","British Indian Ocean Terr.","Brunei Darussalam","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Cape Verde","Cayman Islands","Central African Republic","Chad","Chile","China","Christmas Island","Cocos (Keeling) Islands","Colombia","Comoros","Congo","Cook Islands","Costa Rica","Cote d`Ivoire","Croatia (Hrvatska)","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","East Timor","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Ethiopia","Falkland Islands/Malvinas","Faroe Islands","Fiji","Finland","France","France, Metropolitan","French Guiana","French Polynesia","French Southern Terr.","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guadeloupe","Guam","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Heard &amp; McDonald Is.","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Korea, North","Korea, South","Kuwait","Kyrgyzstan","Lao People`s Dem. Rep.","Latvia","Lebanon","Lesotho","Liberia","Libyan Arab Jamahiriya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Martinique","Mauritania","Mauritius","Mayotte","Micronesia","Moldova","Monaco","Mongolia","Montserrat","Morocco","Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","Netherlands Antilles","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria","Niue","Norfolk Island","Northern Mariana Is.","Norway","Oman","Pakistan","Palau","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Pitcairn","Poland","Portugal","Puerto Rico","Qatar","Reunion","Romania","Russian Federation","Rwanda","Saint Kitts and Nevis","Saint Lucia","St. Vincent &amp; Grenadines","Samoa","San Marino","Sao Tome &amp; Principe","Saudi Arabia","Senegal","Seychelles","Sierra Leone","Singapore","Slovakia (Slovak Republic)","Slovenia","Solomon Islands","Somalia","South Africa","S.Georgia &amp; S.Sandwich Is.","Spain","Sri Lanka","St. Helena","St. Pierre &amp; Miquelon","Sudan","Suriname","Svalbard &amp; Jan Mayen Is.","Swaziland","Sweden","Switzerland","Syrian Arab Republic","Taiwan","Tajikistan","Tanzania","Thailand","Togo","Tokelau","Tonga","Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Turks &amp; Caicos Islands","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","U.S. Minor Outlying Is.","Uruguay","Uzbekistan","Vanuatu","Vatican (Holy See)","Venezuela","Viet Nam","Virgin Islands (British)","Virgin Islands (U.S.)","Wallis &amp; Futuna Is.","Western Sahara","Yemen","Yugoslavia","Zaire","Zambia","Zimbabwe"]],
        ["name" => "Languages","type"=>"List","preset"=>["Abkhaz","Achinese","Acoli","Adangme","Adygei","Afar","Afrihili (Artificial language)","Afrikaans","Afroasiatic (Other)","Akan","Akkadian","Albanian","Aleut","Algonquian (Other)","Altaic (Other)","Amharic","Apache languages","Arabic","Aragonese Spanish","Aramaic","Arapaho","Arawak","Armenian","Artificial (Other)","Assamese","Athapascan (Other)","Australian languages","Austronesian (Other)","Avaric","Avestan","Awadhi","Aymara","Azerbaijani","Bable","Balinese","Baltic (Other)","Baluchi","Bambara","Bamileke languages","Banda","Bantu (Other)","Basa","Bashkir","Basque","Batak","Beja","Belarusian","Bemba","Bengali","Berber (Other)","Bhojpuri","Bihari","Bikol","Bislama","Bosnian","Braj","Breton","Bugis","Bulgarian","Buriat","Burmese","Caddo","Carib","Catalan","Caucasian (Other)","Cebuano","Celtic (Other)","Central American Indian (Other)","Chagatai","Chamic languages","Chamorro","Chechen","Cherokee","Cheyenne","Chibcha","Chinese","Chinook jargon","Chipewyan","Choctaw","Church Slavic","Chuvash","Coptic","Cornish","Corsican","Cree","Creek","Creoles and Pidgins (Other)","Creoles and Pidgins, English-based (Other)","Creoles and Pidgins, French-based (Other)","Creoles and Pidgins, Portuguese-based (Other)","Crimean Tatar","Croatian","Cushitic (Other)","Czech","Dakota","Danish","Dargwa","Dayak","Delaware","Dinka","Divehi","Dogri","Dogrib","Dravidian (Other)","Duala","Dutch","Dutch, Middle (ca. 1050-1350)","Dyula","Dzongkha","Edo","Efik","Egyptian","Ekajuk","Elamite","English","English, Middle (1100-1500)","English, Old (ca. 450-1100)","Esperanto","Estonian","Ethiopic","Ewe","Ewondo","Fang","Fanti","Faroese","Fijian","Finnish","Finno-Ugrian (Other)","Fon","French","French, Middle (ca. 1400-1600)","French, Old (ca. 842-1400)","Frisian","Friulian","Fula","Galician","Ganda","Gayo","Gbaya","Georgian","German","German, Middle High (ca. 1050-1500)","German, Old High (ca. 750-1050)","Germanic (Other)","Gilbertese","Gondi","Gorontalo","Gothic","Grebo","Greek, Ancient (to 1453)","Greek, Modern (1453- )","Guarani","Gujarati","Gwich'in","Gã","Haida","Haitian French Creole","Hausa","Hawaiian","Hebrew","Herero","Hiligaynon","Himachali","Hindi","Hiri Motu","Hittite","Hmong","Hungarian","Hupa","Iban","Icelandic","Ido","Igbo","Ijo","Iloko","Inari Sami","Indic (Other)","Indo-European (Other)","Indonesian","Ingush","Interlingua (International Auxiliary Language Association)","Interlingue","Inuktitut","Inupiaq","Iranian (Other)","Irish","Irish, Middle (ca. 1100-1550)","Irish, Old (to 1100)","Iroquoian (Other)","Italian","Japanese","Javanese","Judeo-Arabic","Judeo-Persian","Kabardian","Kabyle","Kachin","Kalmyk","Kalâtdlisut","Kamba","Kannada","Kanuri","Kara-Kalpak","Karen","Kashmiri","Kawi","Kazakh","Khasi","Khmer","Khoisan (Other)","Khotanese","Kikuyu","Kimbundu","Kinyarwanda","Komi","Kongo","Konkani","Korean","Kpelle","Kru","Kuanyama","Kumyk","Kurdish","Kurukh","Kusaie","Kutenai","Kyrgyz","Ladino","Lahnda","Lamba","Lao","Latin","Latvian","Letzeburgesch","Lezgian","Limburgish","Lingala","Lithuanian","Low German","Lozi","Luba-Katanga","Luba-Lulua","Luiseño","Lule Sami","Lunda","Luo (Kenya and Tanzania)","Lushai","Macedonian","Madurese","Magahi","Maithili","Makasar","Malagasy","Malay","Malayalam","Maltese","Manchu","Mandar","Mandingo","Manipuri","Manobo languages","Manx","Maori","Mapuche","Marathi","Mari","Marshallese","Marwari","Masai","Mayan languages","Mende","Micmac","Minangkabau","Miscellaneous languages","Mohawk","Moldavian","Mon-Khmer (Other)","Mongo-Nkundu","Mongolian","Mooré","Multiple languages","Munda (Other)","Nahuatl","Nauru","Navajo","Ndebele (South Africa)","Ndebele (Zimbabwe)","Ndonga","Neapolitan Italian","Nepali","Newari","Nias","Niger-Kordofanian (Other)","Nilo-Saharan (Other)","Niuean","Nogai","North American Indian (Other)","Northern Sami","Northern Sotho","Norwegian","Norwegian (Bokmål)","Norwegian (Nynorsk)","Nubian languages","Nyamwezi","Nyanja","Nyankole","Nyoro","Nzima","Occitan (post-1500)","Ojibwa","Old Norse","Old Persian (ca. 600-400 B.C.)","Oriya","Oromo","Osage","Ossetic","Otomian languages","Pahlavi","Palauan","Pali","Pampanga","Pangasinan","Panjabi","Papiamento","Papuan (Other)","Persian","Philippine (Other)","Phoenician","Polish","Ponape","Portuguese","Prakrit languages","Provençal (to 1500)","Pushto","Quechua","Raeto-Romance","Rajasthani","Rapanui","Rarotongan","Romance (Other)","Romani","Romanian","Rundi","Russian","Salishan languages","Samaritan Aramaic","Sami","Samoan","Sandawe","Sango (Ubangi Creole)","Sanskrit","Santali","Sardinian","Sasak","Scots","Scottish Gaelic","Selkup","Semitic (Other)","Serbian","Serer","Shan","Shona","Sichuan Yi","Sidamo","Sign languages","Siksika","Sindhi","Sinhalese","Sino-Tibetan (Other)","Siouan (Other)","Skolt Sami","Slave","Slavic (Other)","Slovak","Slovenian","Sogdian","Somali","Songhai","Soninke","Sorbian languages","Sotho","South American Indian (Other)","Southern Sami","Spanish","Sukuma","Sumerian","Sundanese","Susu","Swahili","Swazi","Swedish","Syriac","Tagalog","Tahitian","Tai (Other)","Tajik","Tamashek","Tamil","Tatar","Telugu","Temne","Terena","Tetum","Thai","Tibetan","Tigrinya","Tigré","Tiv","Tlingit","Tok Pisin","Tokelauan","Tonga (Nyasa)","Tongan","Truk","Tsimshian","Tsonga","Tswana","Tumbuka","Tupi languages","Turkish","Turkish, Ottoman","Turkmen","Tuvaluan","Tuvinian","Twi","Udmurt","Ugaritic","Uighur","Ukrainian","Umbundu","Undetermined","Urdu","Uzbek","Vai","Venda","Vietnamese","Volapük","Votic","Wakashan languages","Walamo","Walloon","Waray","Washo","Welsh","Wolof","Xhosa","Yakut","Yao (Africa)","Yapese","Yiddish","Yoruba","Yupik languages","Zande","Zapotec","Zenaga","Zhuang","Zulu","Zuni"]],
        ["name" => "US States","type"=>"List","preset"=>["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Florida","Georgia","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Ohio","Oklahoma","Oregon","Pennsylvania","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virginia","Washington","West Virginia","Wisconsin","Wyoming"]],
    ];

    /**
     * Returns the project this preset is owned by.
     *
     * @return BelongsTo - DESCRIPTION
     */
	public function project() {
        return $this->belongsTo('App\Project','project_id');
    }
}
