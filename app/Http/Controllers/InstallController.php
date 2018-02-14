<?php namespace App\Http\Controllers;

use App\Http\Requests\InstallRequest;
use App\Version;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class InstallController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Install Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles generating the .env file and running the artisan
	| migration so the rest of the controllers can function.  It also creates the
	| first user. And sets the application key, and creates needed folders
	*/

    /**
     * @var string - The version that will be assigned when you install Kora.
     */
	const INSTALLED_VERSION = '3.0';

    /**
     * @var array - Directories that will be created upon installation.
     */
    public $DIRECTORIES = [
        "storage/app/backups",
        "storage/app/backups/files",
		"storage/app/backups/user_upload",
        "storage/app/exodusAssoc",
        "storage/app/exodusAssoc/conversions",
        "storage/app/exodusAssoc/data",
        "storage/app/exports",
        "storage/app/files",
        "storage/app/plugins",
        "storage/app/presetFiles",
        "storage/app/profiles",
		"storage/app/tmpFiles",
		"storage/app/tmpImport",
	];

    /**
     * @var array - Stock option presets for various field types.
     */
    public $STOCKPRESETS = ["URL_URI" => ["type"=>"Text","preset"=>"/^(http|ftp|https):\/\//"],
        "Boolean" => ["type"=>"List","preset"=>"Yes[!]No"],
        "Countries" => ["type"=>"List","preset"=>"United States[!]United Nations[!]Canada[!]Mexico[!]Afghanistan[!]Albania[!]Algeria[!]American Samoa[!]Andorra[!]Angola[!]Anguilla[!]Antarctica[!]Antigua and Barbuda[!]Argentina[!]Armenia[!]Aruba[!]Australia[!]Austria[!]Azerbaijan[!]Bahamas[!]Bahrain[!]Bangladesh[!]Barbados[!]Belarus[!]Belgium[!]Belize[!]Benin[!]Bermuda[!]Bhutan[!]Bolivia[!]Bosnia and Herzegowina[!]Botswana[!]Bouvet Island[!]Brazil[!]British Indian Ocean Terr.[!]Brunei Darussalam[!]Bulgaria[!]Burkina Faso[!]Burundi[!]Cambodia[!]Cameroon[!]Cape Verde[!]Cayman Islands[!]Central African Republic[!]Chad[!]Chile[!]China[!]Christmas Island[!]Cocos (Keeling) Islands[!]Colombia[!]Comoros[!]Congo[!]Cook Islands[!]Costa Rica[!]Cote d`Ivoire[!]Croatia (Hrvatska)[!]Cuba[!]Cyprus[!]Czech Republic[!]Denmark[!]Djibouti[!]Dominica[!]Dominican Republic[!]East Timor[!]Ecuador[!]Egypt[!]El Salvador[!]Equatorial Guinea[!]Eritrea[!]Estonia[!]Ethiopia[!]Falkland Islands/Malvinas[!]Faroe Islands[!]Fiji[!]Finland[!]France[!]France, Metropolitan[!]French Guiana[!]French Polynesia[!]French Southern Terr.[!]Gabon[!]Gambia[!]Georgia[!]Germany[!]Ghana[!]Gibraltar[!]Greece[!]Greenland[!]Grenada[!]Guadeloupe[!]Guam[!]Guatemala[!]Guinea[!]Guinea-Bissau[!]Guyana[!]Haiti[!]Heard &amp; McDonald Is.[!]Honduras[!]Hong Kong[!]Hungary[!]Iceland[!]India[!]Indonesia[!]Iran[!]Iraq[!]Ireland[!]Israel[!]Italy[!]Jamaica[!]Japan[!]Jordan[!]Kazakhstan[!]Kenya[!]Kiribati[!]Korea, North[!]Korea, South[!]Kuwait[!]Kyrgyzstan[!]Lao People`s Dem. Rep.[!]Latvia[!]Lebanon[!]Lesotho[!]Liberia[!]Libyan Arab Jamahiriya[!]Liechtenstein[!]Lithuania[!]Luxembourg[!]Macau[!]Macedonia[!]Madagascar[!]Malawi[!]Malaysia[!]Maldives[!]Mali[!]Malta[!]Marshall Islands[!]Martinique[!]Mauritania[!]Mauritius[!]Mayotte[!]Micronesia[!]Moldova[!]Monaco[!]Mongolia[!]Montserrat[!]Morocco[!]Mozambique[!]Myanmar[!]Namibia[!]Nauru[!]Nepal[!]Netherlands[!]Netherlands Antilles[!]New Caledonia[!]New Zealand[!]Nicaragua[!]Niger[!]Nigeria[!]Niue[!]Norfolk Island[!]Northern Mariana Is.[!]Norway[!]Oman[!]Pakistan[!]Palau[!]Panama[!]Papua New Guinea[!]Paraguay[!]Peru[!]Philippines[!]Pitcairn[!]Poland[!]Portugal[!]Puerto Rico[!]Qatar[!]Reunion[!]Romania[!]Russian Federation[!]Rwanda[!]Saint Kitts and Nevis[!]Saint Lucia[!]St. Vincent &amp; Grenadines[!]Samoa[!]San Marino[!]Sao Tome &amp; Principe[!]Saudi Arabia[!]Senegal[!]Seychelles[!]Sierra Leone[!]Singapore[!]Slovakia (Slovak Republic)[!]Slovenia[!]Solomon Islands[!]Somalia[!]South Africa[!]S.Georgia &amp; S.Sandwich Is.[!]Spain[!]Sri Lanka[!]St. Helena[!]St. Pierre &amp; Miquelon[!]Sudan[!]Suriname[!]Svalbard &amp; Jan Mayen Is.[!]Swaziland[!]Sweden[!]Switzerland[!]Syrian Arab Republic[!]Taiwan[!]Tajikistan[!]Tanzania[!]Thailand[!]Togo[!]Tokelau[!]Tonga[!]Trinidad and Tobago[!]Tunisia[!]Turkey[!]Turkmenistan[!]Turks &amp; Caicos Islands[!]Tuvalu[!]Uganda[!]Ukraine[!]United Arab Emirates[!]United Kingdom[!]U.S. Minor Outlying Is.[!]Uruguay[!]Uzbekistan[!]Vanuatu[!]Vatican (Holy See)[!]Venezuela[!]Viet Nam[!]Virgin Islands (British)[!]Virgin Islands (U.S.)[!]Wallis &amp; Futuna Is.[!]Western Sahara[!]Yemen[!]Yugoslavia[!]Zaire[!]Zambia[!]Zimbabwe"],
        "Languages" => ["type"=>"List","preset"=>"Abkhaz[!]Achinese[!]Acoli[!]Adangme[!]Adygei[!]Afar[!]Afrihili (Artificial language)[!]Afrikaans[!]Afroasiatic (Other)[!]Akan[!]Akkadian[!]Albanian[!]Aleut[!]Algonquian (Other)[!]Altaic (Other)[!]Amharic[!]Apache languages[!]Arabic[!]Aragonese Spanish[!]Aramaic[!]Arapaho[!]Arawak[!]Armenian[!]Artificial (Other)[!]Assamese[!]Athapascan (Other)[!]Australian languages[!]Austronesian (Other)[!]Avaric[!]Avestan[!]Awadhi[!]Aymara[!]Azerbaijani[!]Bable[!]Balinese[!]Baltic (Other)[!]Baluchi[!]Bambara[!]Bamileke languages[!]Banda[!]Bantu (Other)[!]Basa[!]Bashkir[!]Basque[!]Batak[!]Beja[!]Belarusian[!]Bemba[!]Bengali[!]Berber (Other)[!]Bhojpuri[!]Bihari[!]Bikol[!]Bislama[!]Bosnian[!]Braj[!]Breton[!]Bugis[!]Bulgarian[!]Buriat[!]Burmese[!]Caddo[!]Carib[!]Catalan[!]Caucasian (Other)[!]Cebuano[!]Celtic (Other)[!]Central American Indian (Other)[!]Chagatai[!]Chamic languages[!]Chamorro[!]Chechen[!]Cherokee[!]Cheyenne[!]Chibcha[!]Chinese[!]Chinook jargon[!]Chipewyan[!]Choctaw[!]Church Slavic[!]Chuvash[!]Coptic[!]Cornish[!]Corsican[!]Cree[!]Creek[!]Creoles and Pidgins (Other)[!]Creoles and Pidgins, English-based (Other)[!]Creoles and Pidgins, French-based (Other)[!]Creoles and Pidgins, Portuguese-based (Other)[!]Crimean Tatar[!]Croatian[!]Cushitic (Other)[!]Czech[!]Dakota[!]Danish[!]Dargwa[!]Dayak[!]Delaware[!]Dinka[!]Divehi[!]Dogri[!]Dogrib[!]Dravidian (Other)[!]Duala[!]Dutch[!]Dutch, Middle (ca. 1050-1350)[!]Dyula[!]Dzongkha[!]Edo[!]Efik[!]Egyptian[!]Ekajuk[!]Elamite[!]English[!]English, Middle (1100-1500)[!]English, Old (ca. 450-1100)[!]Esperanto[!]Estonian[!]Ethiopic[!]Ewe[!]Ewondo[!]Fang[!]Fanti[!]Faroese[!]Fijian[!]Finnish[!]Finno-Ugrian (Other)[!]Fon[!]French[!]French, Middle (ca. 1400-1600)[!]French, Old (ca. 842-1400)[!]Frisian[!]Friulian[!]Fula[!]Galician[!]Ganda[!]Gayo[!]Gbaya[!]Georgian[!]German[!]German, Middle High (ca. 1050-1500)[!]German, Old High (ca. 750-1050)[!]Germanic (Other)[!]Gilbertese[!]Gondi[!]Gorontalo[!]Gothic[!]Grebo[!]Greek, Ancient (to 1453)[!]Greek, Modern (1453- )[!]Guarani[!]Gujarati[!]Gwich&apos;in[!]G�[!]Haida[!]Haitian French Creole[!]Hausa[!]Hawaiian[!]Hebrew[!]Herero[!]Hiligaynon[!]Himachali[!]Hindi[!]Hiri Motu[!]Hittite[!]Hmong[!]Hungarian[!]Hupa[!]Iban[!]Icelandic[!]Ido[!]Igbo[!]Ijo[!]Iloko[!]Inari Sami[!]Indic (Other)[!]Indo-European (Other)[!]Indonesian[!]Ingush[!]Interlingua (International Auxiliary Language Association)[!]Interlingue[!]Inuktitut[!]Inupiaq[!]Iranian (Other)[!]Irish[!]Irish, Middle (ca. 1100-1550)[!]Irish, Old (to 1100)[!]Iroquoian (Other)[!]Italian[!]Japanese[!]Javanese[!]Judeo-Arabic[!]Judeo-Persian[!]Kabardian[!]Kabyle[!]Kachin[!]Kalmyk[!]Kal�tdlisut[!]Kamba[!]Kannada[!]Kanuri[!]Kara-Kalpak[!]Karen[!]Kashmiri[!]Kawi[!]Kazakh[!]Khasi[!]Khmer[!]Khoisan (Other)[!]Khotanese[!]Kikuyu[!]Kimbundu[!]Kinyarwanda[!]Komi[!]Kongo[!]Konkani[!]Korean[!]Kpelle[!]Kru[!]Kuanyama[!]Kumyk[!]Kurdish[!]Kurukh[!]Kusaie[!]Kutenai[!]Kyrgyz[!]Ladino[!]Lahnda[!]Lamba[!]Lao[!]Latin[!]Latvian[!]Letzeburgesch[!]Lezgian[!]Limburgish[!]Lingala[!]Lithuanian[!]Low German[!]Lozi[!]Luba-Katanga[!]Luba-Lulua[!]Luise�o[!]Lule Sami[!]Lunda[!]Luo (Kenya and Tanzania)[!]Lushai[!]Macedonian[!]Madurese[!]Magahi[!]Maithili[!]Makasar[!]Malagasy[!]Malay[!]Malayalam[!]Maltese[!]Manchu[!]Mandar[!]Mandingo[!]Manipuri[!]Manobo languages[!]Manx[!]Maori[!]Mapuche[!]Marathi[!]Mari[!]Marshallese[!]Marwari[!]Masai[!]Mayan languages[!]Mende[!]Micmac[!]Minangkabau[!]Miscellaneous languages[!]Mohawk[!]Moldavian[!]Mon-Khmer (Other)[!]Mongo-Nkundu[!]Mongolian[!]Moor�[!]Multiple languages[!]Munda (Other)[!]Nahuatl[!]Nauru[!]Navajo[!]Ndebele (South Africa)[!]Ndebele (Zimbabwe)[!]Ndonga[!]Neapolitan Italian[!]Nepali[!]Newari[!]Nias[!]Niger-Kordofanian (Other)[!]Nilo-Saharan (Other)[!]Niuean[!]Nogai[!]North American Indian (Other)[!]Northern Sami[!]Northern Sotho[!]Norwegian[!]Norwegian (Bokm�l)[!]Norwegian (Nynorsk)[!]Nubian languages[!]Nyamwezi[!]Nyanja[!]Nyankole[!]Nyoro[!]Nzima[!]Occitan (post-1500)[!]Ojibwa[!]Old Norse[!]Old Persian (ca. 600-400 B.C.)[!]Oriya[!]Oromo[!]Osage[!]Ossetic[!]Otomian languages[!]Pahlavi[!]Palauan[!]Pali[!]Pampanga[!]Pangasinan[!]Panjabi[!]Papiamento[!]Papuan (Other)[!]Persian[!]Philippine (Other)[!]Phoenician[!]Polish[!]Ponape[!]Portuguese[!]Prakrit languages[!]Proven�al (to 1500)[!]Pushto[!]Quechua[!]Raeto-Romance[!]Rajasthani[!]Rapanui[!]Rarotongan[!]Romance (Other)[!]Romani[!]Romanian[!]Rundi[!]Russian[!]Salishan languages[!]Samaritan Aramaic[!]Sami[!]Samoan[!]Sandawe[!]Sango (Ubangi Creole)[!]Sanskrit[!]Santali[!]Sardinian[!]Sasak[!]Scots[!]Scottish Gaelic[!]Selkup[!]Semitic (Other)[!]Serbian[!]Serer[!]Shan[!]Shona[!]Sichuan Yi[!]Sidamo[!]Sign languages[!]Siksika[!]Sindhi[!]Sinhalese[!]Sino-Tibetan (Other)[!]Siouan (Other)[!]Skolt Sami[!]Slave[!]Slavic (Other)[!]Slovak[!]Slovenian[!]Sogdian[!]Somali[!]Songhai[!]Soninke[!]Sorbian languages[!]Sotho[!]South American Indian (Other)[!]Southern Sami[!]Spanish[!]Sukuma[!]Sumerian[!]Sundanese[!]Susu[!]Swahili[!]Swazi[!]Swedish[!]Syriac[!]Tagalog[!]Tahitian[!]Tai (Other)[!]Tajik[!]Tamashek[!]Tamil[!]Tatar[!]Telugu[!]Temne[!]Terena[!]Tetum[!]Thai[!]Tibetan[!]Tigrinya[!]Tigr�[!]Tiv[!]Tlingit[!]Tok Pisin[!]Tokelauan[!]Tonga (Nyasa)[!]Tongan[!]Truk[!]Tsimshian[!]Tsonga[!]Tswana[!]Tumbuka[!]Tupi languages[!]Turkish[!]Turkish, Ottoman[!]Turkmen[!]Tuvaluan[!]Tuvinian[!]Twi[!]Udmurt[!]Ugaritic[!]Uighur[!]Ukrainian[!]Umbundu[!]Undetermined[!]Urdu[!]Uzbek[!]Vai[!]Venda[!]Vietnamese[!]Volap�k[!]Votic[!]Wakashan languages[!]Walamo[!]Walloon[!]Waray[!]Washo[!]Welsh[!]Wolof[!]Xhosa[!]Yakut[!]Yao (Africa)[!]Yapese[!]Yiddish[!]Yoruba[!]Yupik languages[!]Zande[!]Zapotec[!]Zenaga[!]Zhuang[!]Zulu[!]Zuni"],
        "US States" => ["type"=>"List","preset"=>"Alabama[!]Alaska[!]Arizona[!]Arkansas[!]California[!]Colorado[!]Connecticut[!]Delaware[!]District of Columbia[!]Florida[!]Georgia[!]Hawaii[!]Idaho[!]Illinois[!]Indiana[!]Iowa[!]Kansas[!]Kentucky[!]Louisiana[!]Maine[!]Maryland[!]Massachusetts[!]Michigan[!]Minnesota[!]Mississippi[!]Missouri[!]Montana[!]Nebraska[!]Nevada[!]New Hampshire[!]New Jersey[!]New Mexico[!]New York[!]North Carolina[!]North Dakota[!]Ohio[!]Oklahoma[!]Oregon[!]Pennsylvania[!]Rhode Island[!]South Carolina[!]South Dakota[!]Tennessee[!]Texas[!]Utah[!]Vermont[!]Virginia[!]Washington[!]West Virginia[!]Wisconsin[!]Wyoming"],
        "US Holidays 2018" => ["type"=>"Schedule","preset"=>"New Years Day: 01/01/2018 - 01/01/2018[!]Martin Luther King Day: 01/15/2018 - 01/15/2018[!]Presidents' Day: 02/19/2018 - 02/19/2018[!]Mother's Day: 05/13/2018 - 05/13/2018[!]Father's Day: 06/17/2018 - 06/17/2018[!]Independence Day: 07/04/2018 - 07/04/2018[!]Labor Day: 09/03/2018 - 09/03/2018[!]Columbus Day: 10/08/2018 - 10/08/2018[!]Veterans Day: 11/12/2018 - 11/12/2018[!]Thanksgiving: 11/22/2018 - 11/22/2018[!]Christmas: 12/25/2018 - 12/25/2018"],
        "US Capitols" => ["type"=>"Geolocator","preset"=>"[Desc]Montgomery, Alabama[Desc][LatLon]32.361538,-86.279118[LatLon][UTM]16S:567823.38838923,3580738.9844514[UTM][Address] East 5th Street Montgomery Alabama[Address][!][Desc]Juneau, Alaska[Desc][LatLon]58.301935,-134.419740[LatLon][UTM]8V:534009.26096904,6462472.8464997[UTM][Address]709 West 9th Street Juneau Alaska[Address][!][Desc]Phoenix, Arizona[Desc][LatLon]33.448457,-112.073844[LatLon][UTM]12S:400194.21279718,3701520.2757013[UTM][Address]Suite 1400 North Central Avenue Phoenix Arizona[Address][!][Desc]Little Rock, Arkansas[Desc][LatLon]34.736009,-92.331122[LatLon][UTM]15S:561232.09562153,3843971.7628186[UTM][Address] West 18th Street  Arkansas[Address][!][Desc]Sacramento, California[Desc][LatLon]38.555605,-121.468926[LatLon][UTM]10S:633407.27512251,4268574.590979[UTM][Address] X Street Y Street Alley Sacramento California[Address][!][Desc]Denver, Colorado[Desc][LatLon]39.7391667,-104.984167[LatLon][UTM]13S:501356.62832259,4398808.0467364[UTM][Address]200 East Colfax Avenue Denver Colorado[Address][!][Desc]Hartford, Connecticut[Desc][LatLon]41.767,-72.677[LatLon][UTM]18T:693091.61449858,4626515.1509541[UTM][Address] Haynes Street City Of Hartford Connecticut[Address][!][Desc]Dover, Delaware[Desc][LatLon]39.161921,-75.526755[LatLon][UTM]18S:454491.37347078,4334877.4920692[UTM][Address] North Bradford Street Dover Delaware[Address][!][Desc]Tallahassee, Florida[Desc][LatLon]30.4518,-84.27277[LatLon][UTM]16R:761883.81679029,3372010.5037012[UTM][Address]902 Martin Street Tallahassee Florida[Address][!][Desc]Atlanta, Georgia[Desc][LatLon]33.76,-84.39[LatLon][UTM]16S:741735.79582188,3738606.7627897[UTM][Address]196 Ted Turner Drive Northwest Atlanta Georgia[Address][!][Desc]Honolulu, Hawaii[Desc][LatLon]21.30895,-157.826182[LatLon][UTM]4Q:621747.01926081,2356793.8454331[UTM][Address] Mamane Place Honolulu Hawaii[Address][!][Desc]Boise, Idaho[Desc][LatLon]43.613739,-116.237651[LatLon][UTM]11T:561515.86953992,4829255.4444125[UTM][Address] Gage Street Boise City Idaho[Address][!][Desc]Springfield, Illinois[Desc][LatLon]39.783250,-89.650373[LatLon][UTM]16S:273036.44670432,4407060.8758545[UTM][Address] East Laurel Street Springfield Illinois[Address][!][Desc]Indianapolis, Indiana[Desc][LatLon]39.790942,-86.147685[LatLon][UTM]16S:572975.22719286,4404901.6314715[UTM][Address] East 17th Street Indianapolis Indiana[Address][!][Desc]Des Moines, Iowa[Desc][LatLon]41.590939,-93.620866[LatLon][UTM]15T:448253.23858349,4604546.3882494[UTM][Address] 2nd Avenue Des Moines Iowa[Address][!][Desc]Topeka, Kansas[Desc][LatLon]39.04,-95.69[LatLon][UTM]15S:267181.5390966,4324659.2616614[UTM][Address]1018 Southwest 15th Street Topeka Kansas[Address][!][Desc]Frankfort, Kentucky[Desc][LatLon]38.197274,-84.86311[LatLon][UTM]16S:687119.84422167,4229861.6492636[UTM][Address] East Main Street Frankfort Kentucky[Address][!][Desc]Baton Rouge, Louisiana[Desc][LatLon]30.45809,-91.140229[LatLon][UTM]15R:678556.42591707,3371016.4979645[UTM][Address] North Foster Drive Baton Rouge Louisiana[Address][!][Desc]Augusta, Maine[Desc][LatLon]44.323535,-69.765261[LatLon][UTM]19T:438980.21801941,4908092.9149464[UTM][Address] Park Street Augusta Maine[Address][!][Desc]Annapolis, Maryland[Desc][LatLon]38.972945,-76.501157[LatLon][UTM]18S:369959.55240987,4314845.850535[UTM][Address]128 Archwood Avenue Annapolis Maryland[Address][!][Desc]Boston, Massachusetts[Desc][LatLon]42.2352,-71.0275[LatLon][UTM]19T:332703.5972572,4677880.84088[UTM][Address] Wesson Avenue  Massachusetts[Address][!][Desc]Lansing, Michigan[Desc][LatLon]42.7335,-84.5467[LatLon][UTM]16T:700831.4265761,4734139.7225832[UTM][Address] East Michigan Avenue Lansing Michigan[Address][!][Desc]Saint Paul, Minnesota[Desc][LatLon]44.95,-93.094[LatLon][UTM]15T:492584.92142831,4977400.3559376[UTM][Address] Robert Street North Saint Paul Minnesota[Address][!][Desc]Jackson, Mississippi[Desc][LatLon]32.320,-90.207[LatLon][UTM]15S:762938.35266383,3579334.2537806[UTM][Address] Carver Street Jackson Mississippi[Address][!][Desc]Jefferson City, Missouri[Desc][LatLon]38.572954,-92.189283[LatLon][UTM]15S:570621.96606259,4269700.1089196[UTM][Address] Edmonds Street Jefferson City Missouri[Address][!][Desc]Helana, Montana[Desc][LatLon]46.595805,-112.027031[LatLon][UTM]12T:421332.72882339,5160761.2480163[UTM][Address] Helena Avenue Helena Montana[Address][!][Desc]Lincoln, Nebraska[Desc][LatLon]40.809868,-96.675345[LatLon][UTM]14T:696075.72531413,4520251.3423297[UTM][Address] J Street Lincoln Nebraska[Address][!][Desc]Carson City, Nevada[Desc][LatLon]39.160949,-119.753877[LatLon][UTM]11S:262059.41187024,4338250.1156982[UTM][Address] East 5th Street Carson City Nevada[Address][!][Desc]Concord, New Hampshire[Desc][LatLon]43.220093,-71.549127[LatLon][UTM]19T:292963.65070103,4788411.2231967[UTM][Address] Curtice Avenue Concord New Hampshire[Address][!][Desc]Trenton, New Jersey[Desc][LatLon]40.221741,-74.756138[LatLon][UTM]18T:520748.50944052,4452397.286396[UTM][Address]450 Ewing Street Trenton New Jersey[Address][!][Desc]Santa Fe, New Mexico[Desc][LatLon]35.667231,-105.964575[LatLon][UTM]13S:412700.06116234,3947469.0280147[UTM][Address] Young Street Santa Fe New Mexico[Address][!][Desc]Albany, New York[Desc][LatLon]42.659829,-73.781339[LatLon][UTM]18T:599877.87894873,4723760.3512143[UTM][Address] Yates Street Albany New York[Address][!][Desc]Raleigh, North Carolina[Desc][LatLon]35.771,-78.638[LatLon][UTM]17S:713514.51156294,3961122.9545538[UTM][Address] South Wilmington Street Raleigh North Carolina[Address][!][Desc]Bismarck, North Dakota[Desc][LatLon]48.813343,-100.779004[LatLon][UTM]14U:369396.37872121,5408232.4739295[UTM][Address] County Road 33  North Dakota[Address][!][Desc]Columbus, Ohio[Desc][LatLon]39.962245,-83.000647[LatLon][UTM]17S:329125.20731903,4425483.3406726[UTM][Address] East Broad Street Columbus Ohio[Address][!][Desc]Oklahoma City, Oklahoma[Desc][LatLon]35.482309,-97.534994[LatLon][UTM]14S:632899.82618685,3927517.786144[UTM][Address] North Mckinley Avenue Oklahoma City Oklahoma[Address][!][Desc]Salem, Oregon[Desc][LatLon]44.931109,-123.029159[LatLon][UTM]10T:497699.07245835,4975297.9434202[UTM][Address]  Salem Oregon[Address][!][Desc]Harrisburg, Pennsylvania[Desc][LatLon]40.269789,-76.875613[LatLon][UTM]18T:340525.35775351,4459389.4206946[UTM][Address] Forster Street Harrisburg Pennsylvania[Address][!][Desc]Providence, Rhode Island[Desc][LatLon]41.82355,-71.422132[LatLon][UTM]19T:298844.83645536,4633021.6717855[UTM][Address] Newton Street Providence Rhode Island[Address][!][Desc]Columbia, South Carolina[Desc][LatLon]34.000,-81.035[LatLon][UTM]17S:496767.82579737,3762156.5296628[UTM][Address]1115 Assembly Street Columbia South Carolina[Address][!][Desc]Pierre, South Dakota[Desc][LatLon]44.367966,-100.336378[LatLon][UTM]14T:393521.24858888,4913611.737334[UTM][Address] East Robinson Avenue Pierre South Dakota[Address][!][Desc]Nashville, Tennessee[Desc][LatLon]36.165,-86.784[LatLon][UTM]16S:519426.94669423,4002271.2269574[UTM][Address] 7th Avenue North Nashville-Davidson Tennessee[Address][!][Desc]Austin, Texas[Desc][LatLon]30.266667,-97.75[LatLon][UTM]14R:620240.70200607,3348995.9735886[UTM][Address]607 West 3rd Street Austin Texas[Address][!][Desc]Salt Lake City, Utah[Desc][LatLon]40.7547,-111.892622[LatLon][UTM]12T:424651.03790536,4511910.1988511[UTM][Address] 700 South Salt Lake City Utah[Address][!][Desc]Montpelier, Vermont[Desc][LatLon]44.26639,-72.57194[LatLon][UTM]18T:693796.0175554,4904327.9430711[UTM][Address]15 Winter Street Montpelier Vermont[Address][!][Desc]Richmond, Virginia[Desc][LatLon]37.54,-77.46[LatLon][UTM]18S:282659.10446272,4157622.9853212[UTM][Address] Lakeview Avenue Richmond City Virginia[Address][!][Desc]Olympia, Washington[Desc][LatLon]47.042418,-122.893077[LatLon][UTM]10T:508122.44663845,5209883.4331402[UTM][Address] Plum Street Southeast Olympia Washington[Address][!][Desc]Charleston, West Virginia[Desc][LatLon]38.349497,-81.633294[LatLon][UTM]17S:444663.13148926,4244783.347957[UTM][Address] Hale Street Charleston West Virginia[Address][!][Desc]Madison, Wisconsin[Desc][LatLon]43.074722,-89.384444[LatLon][UTM]16T:305879.7197932,4771872.0721079[UTM][Address]2 East Main Street Madison Wisconsin[Address][!][Desc]Cheyenne, Wyoming[Desc][LatLon]41.145548,-104.802042[LatLon][UTM]13T:516611.89796343,4554933.3575248[UTM][Address]1525 East Pershing Boulevard Cheyenne Wyoming[Address]"]
    ];

    /**
     * Gets home view for the uninstalled welcome page.
     *
     * @return View
     */
    public function helloworld() {
        if(file_exists("../.env"))
            return redirect('/');

        return view('install.helloworld');
    }

    /**
     * Gets home view for the installation page.
     *
     * @return View
     */
	public function index() {
		if(file_exists("../.env"))
			return redirect('/');

		return view('install.install');
	}

    /**
     * Install Kora 3 - Creates the env file.
     *
     * @param  InstallRequest $request
     * @return JsonResponse
     */
	public function install(InstallRequest $request) {
        Log::info("Beginning Installation");

        //Write the env file
        $envData = $this->envBuilder($request);
        try {
            Log::info("Beginning ENV Write");
            $envfile = fopen("../.env", "w");

            fwrite($envfile, $envData);

            fclose($envfile);
            chmod("../.env",0660);
            Log::info("Generating App Key");
            Artisan::call('key:generate');
            Log::info("Ending ENV Write ");
        } catch(\Exception $e) { //Most likely if the file is owned by another user or PHP doesn't have permission
            Log::info($e);
            $this->resetInstall();
            return response()->json(["status"=>false,"message"=>"env_cant_write"],500);
        }

        //Since we need to refresh the env variables before we install.
        return response()->json(["status"=>true,"message"=>"env_saved"],200);
    }

    /**
     * Finish install of Kora 3 - Creates DB, directories, default values, admin user.
     *
     * @param  InstallRequest $request
     * @return Redirect
     */
    public function installPartTwo(InstallRequest $request) {
        //Test out the DB connection
        $dbc = null;
        try{
            Log::info("Beginning DB Test");
            switch(env('DB_DEFAULT')) {
                case "mysql":
                    $dbc = new \PDO('mysql:host='.env('DB_HOST').';dbname='.env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
                    break;
                case "pgsql":
                    $dbc = new \PDO('pgsql:host='.env('DB_HOST').';dbname='.env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
                    break;
                case "sqlsrv":
                    $dbc = new \PDO('pgsql:Server='.env('DB_HOST').';Databasee='.env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
                    break;
                default:
                    Log::info("Default type is missing. This is most likely an issue with Laravel failing to read the new ENV file.");
                    $this->resetInstall($dbc);
                    return redirect('/install')->withInput(Input::all())->with('k3_global_error', 'database_env_issue');
            }
            Log::info("Ending DB Test");
        } catch(\PDOException $e) {
            Log::info($e);
            $this->resetInstall();
            return redirect('/install')->withInput(Input::all())->with('k3_global_error', 'database_connection_failed');
        }

        //Install database tables
        try {
            Log::info("Beginning Artisan Migrate");
            Artisan::call("migrate", array('--force' => true));
            Log::info("Ending Artisan Migrate");
        } catch(\Exception $e) {
            Log::info($e);
            $this->resetInstall($dbc);
            return redirect('/install')->withInput(Input::all())->with('k3_global_error', 'db_creation_failed');
        }
        try {
            Log::info("Beginning Artisan Key Creation");
            Artisan::call("key:generate");
            Log::info("Ending Artisan Key Creation");
        } catch(\Exception $e) {
            Log::info($e);
            $this->resetInstall($dbc);
            return redirect('/install')->withInput(Input::all())->with('k3_global_error', 'key_generation_fail');
        }
        try {
            Log::info("Beginning Version Set");
            $v = new Version();
            $v->version = InstallController::INSTALLED_VERSION;
            $v->save();
            Log::info("Ending Version Set");
        } catch(\Exception $e) {
            Log::info($e);
            $this->resetInstall($dbc);
            return redirect('/install')->withInput(Input::all())->with('k3_global_error', 'k3_version_issue');
        }

        //Create all the needed directories for storage
        try {
            Log::info("Beginning Directory Creation");
            $this->createDirectories();
            Log::info("Ending Directory Creation");
        } catch(\Exception $e) {
            Log::info($e);
            $this->resetInstall($dbc);
            return redirect('/install')->withInput(Input::all())->with('k3_global_error', 'install_directories_failed');
        }

        //Create admin user
        try {
            Log::info("Beginning Admin Construction");
            $this->makeAdmin($request);
            Log::info("Ending Admin Construction");
        } catch(\Exception $e) {
            Log::info($e);
            $this->resetInstall($dbc);
            return redirect('/install')->withInput(Input::all())->with('k3_global_error', 'admin_creation_fail');
        }

        try {
            Log::info("Beginning Preset Creation");
            foreach($this->STOCKPRESETS as $name => $info) {
                $pid = null;
                $type = $info['type'];
                $preset = $info['preset'];

                \App\OptionPreset::create(compact("name","pid","type","preset"));
            }
            Log::info("Ending Preset Creation");
        } catch(\Exception $e) {
            Log::info($e);
            $this->resetInstall($dbc);
            return redirect('/install')->withInput(Input::all())->with('k3_global_error', 'stock_presets_failed');
        }

        //CLOSE THE CONNECTION
        $dbc = null;

        Log::info("Ending Installation");
        return redirect('/')->with('k3_global_success', 'kora_success_install');
    }

    /**
     * Build the text for the env config file.
     *
     * @param  InstallRequest $request
     * @return string - The text to write to file
     */
    private function envBuilder(InstallRequest $request){
        $baseurl = $request->baseurl_url;
        //Check if http:// is included in the base URL, and addi it if missing
        if(!preg_match("/(http)(.*)/",$baseurl))
            $baseurl = "http://".$baseurl;

        //Check for trailing slashes
        if(substr($baseurl,-1) != "/")
            $baseurl = $baseurl."/";

        $storageurl = $request->baseurl_storage;
        //Check if http:// is included in the base URL, and addi it if missing
        if(!preg_match("/(http)(.*)/",$storageurl))
            $storageurl = "http://".$storageurl;

        //Check for trailing slashes
        if(substr($storageurl,-1) != "/")
            $storageurl = $storageurl."/";

        $layout =
            "APP_ENV=local\n".
            "APP_DEBUG=false\n".
            "APP_KEY=\n\n".

            "DB_HOST=" . $request->db_host . "\n" .
            "DB_DATABASE=" . $request->db_database . "\n" .
            "DB_USERNAME=" . $request->db_username . "\n" .
            "DB_PASSWORD=" . $request->db_password . "\n" .
            "DB_DEFAULT=" . $request->db_driver . "\n" .
            "DB_PREFIX=" . $request->db_prefix . "\n\n" .

            "MAIL_HOST=" . $request->mail_host . "\n" .
            "MAIL_FROM_ADDRESS=" . $request->mail_from_address . "\n" .
            "MAIL_FROM_NAME=\"" . $request->mail_from_name . "\"\n" .
            "MAIL_USER=" . $request->mail_username . "\n" .
            "MAIL_PASSWORD=" . $request->mail_password . "\n\n" .

            "CACHE_DRIVER=file\n".
            "SESSION_DRIVER=file\n\n".

            "BASE_URL=" . $baseurl . "\n" .
            "STORAGE_URL=" . $storageurl . "\n" .
            "BASE_PATH=" . $request->basepath . "\n\n" .

            "RECAPTCHA_PUBLIC_KEY=" . $request->recaptcha_public_key . "\n" .
            "RECAPTCHA_PRIVATE_KEY=" . $request->recaptcha_private_key;

        return $layout;
    }

    /**
     * Creates all the directories for the installation process.
     *
     * @return bool - Directories created
     */
    private function createDirectories() {
        foreach($this->DIRECTORIES as $dir) {
            if(!file_exists(config('app.base_path').$dir)) {
                try {
                    mkdir(config('app.base_path') . $dir, 0775); //Notice the permission that is set and if it's OK!
                } catch(\Exception $e) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Create the admin user for the installation.
     *
     * @param  InstallRequest $request
     */
    private function makeAdmin(InstallRequest $request) {
        $username = $request->user_username;
        $first_name = $request->user_firstname;
        $last_name = $request->user_lastname;
        $email = $request->user_email;
        $password = bcrypt($request->user_password);
        $organization = $request->user_organization;
        $language = $request->user_language;

        $newuser = \App\User::create(compact("username","first_name","last_name","email","password","organization","language"));
        $newuser->active = 1;
        $newuser->admin = 1;
        $newuser->save();

        if(!is_null($request->file('user_profile'))) {
            $file = $request->file('user_profile');
            $pDir = config('app.base_path') . 'storage/app/profiles/1/';

            $newFilename = $file->getClientOriginalName();
            $newuser->profile = $newFilename;
            $newuser->save();

            //move photo and return new path
            $file->move($pDir,$newFilename);
        }
    }

    /**
     * Rolls back the install process so it can be rerun.
     *
     * @param  \PDO $dbc - Connection to the DB
     */
    private function resetInstall(\PDO $dbc = null) {
        //Delete the ENV
        if(file_exists("../.env"))
            unlink("../.env");

        //Empty the Database
        if(!is_null($dbc)) {
            if($result = $dbc->query("SHOW TABLES")) {
                while($row = $result->fetch(\PDO::FETCH_NUM)) {
                    $dbc->query('DROP TABLE IF EXISTS ' . $row[0]);
                }
            }
        }

        //Close the connection
        $dbc = null;
    }

    /**
     * Edits recaptcha and mail options in the ENV configuration file.
     *
     * @return View
     */
    public function editEnvConfigs() {
        if(!Auth::check())
            return redirect("/");

        if(!Auth::user()->admin) {
            flash()->overlay("You must be an admin to see this","Whoops");
            return redirect("/");
        }

        $configs = array(
            ['title'=>'Recaptcha Private Key', 'slug'=>'recaptcha_private', 'value'=>config('auth.recap_private')],
            ['title'=>'Recaptcha Public Key',  'slug'=>'recaptcha_public',  'value'=>config('auth.recap_public')],
            ['title'=>'Mail Host',             'slug'=>'mail_host',         'value'=>config('mail.host')],
            ['title'=>'Mail From Address',     'slug'=>'mail_address',      'value'=>config('mail.from.address')],
            ['title'=>'Mail From Name',        'slug'=>'mail_name',         'value'=>config('mail.from.name')],
            ['title'=>'Mail User',             'slug'=>'mail_user',         'value'=>config('mail.username')],
            ['title'=>'Mail Password',         'slug'=>'mail_password',     'value'=>config('mail.password')],
        );

        return view('install.config',compact('configs'));
    }

    /**
     * Updates recaptcha and mail options in the ENV configuration file.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function updateEnvConfigs(Request $request) {
        if(!Auth::user()->admin)
            return redirect('projects')->with('k3_global_error', 'not_admin');

        if(config('app.debug'))
            $debug = 'true';
        else
            $debug = 'false';

        $layout = "APP_ENV=" . config('app.env') . "\n".
            "APP_DEBUG=" . $debug . "\n".
            "APP_KEY=" . config('app.key') . "\n\n".

            "DB_HOST=" . env('DB_HOST') . "\n" .
            "DB_DATABASE=" . env('DB_DATABASE') . "\n" .
            "DB_USERNAME=" . env('DB_USERNAME') . "\n" .
            "DB_PASSWORD=" . env('DB_PASSWORD') . "\n" .
            "DB_DEFAULT=" . env('DB_DEFAULT') . "\n" .
            "DB_PREFIX=" . env('DB_PREFIX') . "\n\n" .

            "MAIL_HOST=" . $request->mail_host . "\n" .
            "MAIL_FROM_ADDRESS=" . $request->mail_address . "\n" .
            "MAIL_FROM_NAME=\"" . $request->mail_name . "\"\n" .
            "MAIL_USER=" . $request->mail_user . "\n" .
            "MAIL_PASSWORD=" . $request->mail_password . "\n\n" .

            "CACHE_DRIVER=" . config('cache.default') . "\n".
            "SESSION_DRIVER=" . config('session.driver') . "\n\n".

            "BASE_URL=" . config('app.url') . "\n" .
            "STORAGE_URL=" . config('app.storage_url') . "\n" .
            "BASE_PATH=" . config('app.base_path') . "\n\n" .

            "RECAPTCHA_PUBLIC_KEY=" . $request->recaptcha_public . "\n" .
            "RECAPTCHA_PRIVATE_KEY=" . $request->recaptcha_private;

        try {
            Log::info("Beginning ENV Write");
            $envfile = fopen("../.env", "w");

            fwrite($envfile, $layout);

            fclose($envfile);
            chmod("../.env",0660);
            Log::info("Ending ENV Write");
        } catch(\Exception $e) { //Most likely if the file is owned by another user or PHP doesn't have permission
            Log::info($e);
            return redirect('install/config')->with('k3_global_error', 'env_cant_write');
        }

        return redirect('install/config')->with('k3_global_success', 'kora_config_updated');
    }
}