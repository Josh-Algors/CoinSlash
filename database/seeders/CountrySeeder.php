<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $country_list = [
            ["name"=>"Afghanistan",  "shortname"=>"AFG", "code" => "93"], 
            ["name"=>"Åland Islands",  "shortname"=>"ALA", "code" => "358 18"], 
            ["name"=>"Albania",  "shortname"=>"ALB", "code" => "355"], 
            ["name"=>"Algeria",  "shortname"=>"DZA", "code" => "213"], 
            ["name"=>"American Samoa",  "shortname"=>"ASM", "code" => "1 684"], 
            ["name"=>"Andorra",  "shortname"=>"AND", "code" => "376"], 
            ["name"=>"Angola",  "shortname"=>"AGO", "code" => "244"], 
            ["name"=>"Anguilla",  "shortname"=>"AIA", "code" => "1 264"], 
            ["name"=>"Antarctica",  "shortname"=>"ATA", "code" => "6721"], 
            ["name"=>"Antigua and Barbuda",  "shortname"=>"ATG", "code" => "1 268"], 
            ["name"=>"Argentina",  "shortname"=>"ARG", "code" => "54"], 
            ["name"=>"Armenia",  "shortname"=>"ARM", "code" => "374"], 
            ["name"=>"Aruba",  "shortname"=>"ABW", "code" => "297"], 
            ["name"=>"Australia",  "shortname"=>"AUS", "code" => "61"], 
            ["name"=>"Austria",  "shortname"=>"AUT", "code" => "43"], 
            ["name"=>"Azerbaijan",  "shortname"=>"AZE", "code" => "994"], 
            ["name"=>"Bahamas",  "shortname"=>"BHS", "code" => "1 242"], 
            ["name"=>"Bahrain",  "shortname"=>"BHR", "code" => "973"], 
            ["name"=>"Bangladesh",  "shortname"=>"BGD", "code" => "880"], 
            ["name"=>"Barbados",  "shortname"=>"BRB", "code" => "1 246"], 
            ["name"=>"Belarus",  "shortname"=>"BLR", "code" => "375"], 
            ["name"=>"Belgium",  "shortname"=>"BEL", "code" => "32"], 
            ["name"=>"Belize",  "shortname"=>"BLZ", "code" => "501"], 
            ["name"=>"Benin",  "shortname"=>"BEN", "code" => "229"], 
            ["name"=>"Bermuda",  "shortname"=>"BMU", "code" => "1 441"], 
            ["name"=>"Bhutan",  "shortname"=>"BTN", "code" => "975"], 
            ["name"=>"Bolivia",  "shortname"=>"BOL", "code" => "591"], 
            ["name"=>"Bonaire, Sint Eustatius and Saba",  "shortname"=>"BES", "code" => "5997"], 
            ["name"=>"Bosnia and Herzegovina",  "shortname"=>"BIH", "code" => "387"], 
            ["name"=>"Botswana",  "shortname"=>"BWA", "code" => "267"], 
            ["name"=>"Bouvet Island",  "shortname"=>"BVT", "code" => "47"], 
            ["name"=>"Brazil",  "shortname"=>"BRA", "code" => "55"], 
            ["name"=>"British Indian Ocean Territory",  "shortname"=>"IOT", "code" => "246"], 
            ["name"=>"Brunei Darussalam",  "shortname"=>"BRN", "code" => "673"], 
            ["name"=>"Bulgaria",  "shortname"=>"BGR", "code" => "359"], 
            ["name"=>"Burkina Faso",  "shortname"=>"BFA", "code" => "226"], 
            ["name"=>"Burundi",  "shortname"=>"BDI", "code" => "257"], 
            ["name"=>"Cambodia",  "shortname"=>"KHM", "code" => "855"], 
            ["name"=>"Cameroon",  "shortname"=>"CMR", "code" => "237"], 
            ["name"=>"Canada",  "shortname"=>"CAN", "code" => "1"], 
            ["name"=>"Cape Verde",  "shortname"=>"CPV", "code" => "238"], 
            ["name"=>"Cayman Islands",  "shortname"=>"CYM", "code" => "1 345"], 
            ["name"=>"Central African Republic",  "shortname"=>"CAF", "code" => "236"], 
            ["name"=>"Chad",  "shortname"=>"TCD", "code" => "235"], 
            ["name"=>"Chile",  "shortname"=>"CHL", "code" => "56"], 
            ["name"=>"China",  "shortname"=>"CHN", "code" => "86"], 
            ["name"=>"Christmas Island",  "shortname"=>"CXR", "code" => "61"], 
            ["name"=>"Cocos (Keeling) Islands",  "shortname"=>"CCK", "code" => "61"], 
            ["name"=>"Colombia",  "shortname"=>"COL", "code" => "57"], 
            ["name"=>"Comoros",  "shortname"=>"COM", "code" => "269"], 
            ["name"=>"Congo, the Democratic Republic of the",  "shortname"=>"COD", "code" => "242"], 
            ["name"=>"Congo",  "shortname"=>"COG", "code" => "243"], 
            ["name"=>"Cook Islands",  "shortname"=>"COK", "code" => "682"], 
            ["name"=>"Costa Rica",  "shortname"=>"CRI", "code" => "506"], 
            ["name"=>"Côte d'Ivoire",  "shortname"=>"CIV", "code" => "225"], 
            ["name"=>"Croatia",  "shortname"=>"HRV", "code" => "385"], 
            ["name"=>"Cuba",  "shortname"=>"CUB", "code" => "53"], 
            ["name"=>"Curaçao",  "shortname"=>"CUW", "code" => "599"], 
            ["name"=>"Cyprus",  "shortname"=>"CYP", "code" => "357"], 
            ["name"=>"Czech Republic",  "shortname"=>"CZE", "code" => "420"], 
            ["name"=>"Denmark",  "shortname"=>"DNK", "code" => "45"], 
            ["name"=>"Djibouti",  "shortname"=>"DJI", "code" => "253"], 
            ["name"=>"Dominica",  "shortname"=>"DMA", "code" => "1 767"], 
            ["name"=>"Dominican Republic",  "shortname"=>"DOM", "code" => "1 809/829/ 849"], 
            ["name"=>"Ecuador",  "shortname"=>"ECU", "code" => "593"], 
            ["name"=>"Egypt",  "shortname"=>"EGY", "code" => "20"], 
            ["name"=>"El Salvador",  "shortname"=>"SLV", "code" => "503"], 
            ["name"=>"Equatorial Guinea",  "shortname"=>"GNQ", "code" => "240"], 
            ["name"=>"Eritrea",  "shortname"=>"ERI", "code" => "291"], 
            ["name"=>"Estonia",  "shortname"=>"EST", "code" => "372"], 
            ["name"=>"Ethiopia",  "shortname"=>"ETH", "code" => "251"], 
            ["name"=>"Falkland Islands (Malvinas)",  "shortname"=>"FLK", "code" => "500"], 
            ["name"=>"Faroe Islands",  "shortname"=>"FRO", "code" => "298"], 
            ["name"=>"Fiji",  "shortname"=>"FJI", "code" => "679"], 
            ["name"=>"Finland",  "shortname"=>"FIN", "code" => "358"], 
            ["name"=>"France",  "shortname"=>"FRA", "code" => "33"], 
            ["name"=>"French Guiana",  "shortname"=>"GUF", "code" => "594"], 
            ["name"=>"French Polynesia",  "shortname"=>"PYF", "code" => "689"], 
            ["name"=>"French Southern Territories",  "shortname"=>"ATF", "code" => "-"], 
            ["name"=>"Gabon",  "shortname"=>"GAB", "code" => "241"], 
            ["name"=>"Gambia",  "shortname"=>"GMB", "code" => "220"], 
            ["name"=>"Georgia",  "shortname"=>"GEO", "code" => "995"], 
            ["name"=>"Germany",  "shortname"=>"DEU", "code" => "49"], 
            ["name"=>"Ghana",  "shortname"=>"GHA", "code" => "233"], 
            ["name"=>"Gibraltar",  "shortname"=>"GIB", "code" => "350"], 
            ["name"=>"Greece",  "shortname"=>"GRC", "code" => "30"], 
            ["name"=>"Greenland",  "shortname"=>"GRL", "code" => "299"], 
            ["name"=>"Grenada",  "shortname"=>"GRD", "code" => "1 473"], 
            ["name"=>"Guadeloupe",  "shortname"=>"GLP", "code" => "590"], 
            ["name"=>"Guam",  "shortname"=>"GUM", "code" => "1 671"], 
            ["name"=>"Guatemala",  "shortname"=>"GTM", "code" => "502"], 
            ["name"=>"Guernsey",  "shortname"=>"GGY", "code" => "44"], 
            ["name"=>"Guinea-Bissau",  "shortname"=>"GNB", "code" => "245"], 
            ["name"=>"Guinea",  "shortname"=>"GIN", "code" => "224"], 
            ["name"=>"Guyana",  "shortname"=>"GUY", "code" => "592"], 
            ["name"=>"Haiti",  "shortname"=>"HTI", "code" => "509"], 
            ["name"=>"Heard Island and McDonald Islands",  "shortname"=>"HMD", "code" => "1 672"], 
            ["name"=>"Holy See (Vatican City State)",  "shortname"=>"VAT", "code" => "379"], 
            ["name"=>"Honduras",  "shortname"=>"HND", "code" => "504"], 
            ["name"=>"Hong Kong",  "shortname"=>"HKG", "code" => "852"], 
            ["name"=>"Hungary",  "shortname"=>"HUN", "code" => "36"], 
            ["name"=>"Iceland",  "shortname"=>"ISL", "code" => "354"], 
            ["name"=>"India",  "shortname"=>"IND", "code" => "91"], 
            ["name"=>"Indonesia",  "shortname"=>"IDN", "code" => "62"], 
            ["name"=>"Iran, Islamic Republic of",  "shortname"=>"IRN", "code" => "98"], 
            ["name"=>"Iraq",  "shortname"=>"IRQ", "code" => "964"], 
            ["name"=>"Ireland",  "shortname"=>"IRL", "code" => "353"], 
            ["name"=>"Isle of Man",  "shortname"=>"IMN", "code" => "44"], 
            ["name"=>"Israel",  "shortname"=>"ISR", "code" => "972"], 
            ["name"=>"Italy",  "shortname"=>"ITA", "code" => "39"], 
            ["name"=>"Jamaica",  "shortname"=>"JAM", "code" => "1 876"], 
            ["name"=>"Japan",  "shortname"=>"JPN", "code" => "81"], 
            ["name"=>"Jersey",  "shortname"=>"JEY", "code" => "44"], 
            ["name"=>"Jordan",  "shortname"=>"JOR", "code" => "962"], 
            ["name"=>"Kazakhstan",  "shortname"=>"KAZ", "code" => "7"], 
            ["name"=>"Kenya",  "shortname"=>"KEN", "code" => "254"], 
            ["name"=>"Kiribati",  "shortname"=>"KIR", "code" => "686"], 
            ["name"=>"Korea, Democratic People's Republic of",  "shortname"=>"PRK", "code" => "850"], 
            ["name"=>"Korea, Republic of",  "shortname"=>"KOR", "code" => "82"], 
            ["name"=>"Kuwait",  "shortname"=>"KWT", "code" => "965"], 
            ["name"=>"Kyrgyzstan",  "shortname"=>"KGZ", "code" => "996"], 
            ["name"=>"Lao People's Democratic Republic",  "shortname"=>"LAO", "code" => "856"], 
            ["name"=>"Latvia",  "shortname"=>"LVA", "code" => "371"], 
            ["name"=>"Lebanon",  "shortname"=>"LBN", "code" => "961"], 
            ["name"=>"Lesotho",  "shortname"=>"LSO", "code" => "266"], 
            ["name"=>"Liberia",  "shortname"=>"LBR", "code" => "231"], 
            ["name"=>"Libyan Arab Jamahiriya",  "shortname"=>"LBY", "code" => "218"], 
            ["name"=>"Liechtenstein",  "shortname"=>"LIE", "code" => "423"], 
            ["name"=>"Lithuania",  "shortname"=>"LTU", "code" => "370"], 
            ["name"=>"Luxembourg",  "shortname"=>"LUX", "code" => "352"], 
            ["name"=>"Macao",  "shortname"=>"MAC", "code" => "853"], 
            ["name"=>"Macedonia, the Former Yugoslav Republic of",  "shortname"=>"MKD", "code" => "389"], 
            ["name"=>"Madagascar",  "shortname"=>"MDG", "code" => "261"], 
            ["name"=>"Malawi",  "shortname"=>"MWI", "code" => "265"], 
            ["name"=>"Malaysia",  "shortname"=>"MYS", "code" => "60"], 
            ["name"=>"Maldives",  "shortname"=>"MDV", "code" => "960"], 
            ["name"=>"Mali",  "shortname"=>"MLI", "code" => "223"], 
            ["name"=>"Malta",  "shortname"=>"MLT", "code" => "356"], 
            ["name"=>"Marshall Islands",  "shortname"=>"MHL", "code" => "692"], 
            ["name"=>"Martinique",  "shortname"=>"MTQ", "code" => "596"], 
            ["name"=>"Mauritania",  "shortname"=>"MRT", "code" => "222"], 
            ["name"=>"Mauritius",  "shortname"=>"MUS", "code" => "230"], 
            ["name"=>"Mayotte",  "shortname"=>"MYT", "code" => "262"], 
            ["name"=>"Mexico",  "shortname"=>"MEX", "code" => "52"], 
            ["name"=>"Micronesia, Federated States of",  "shortname"=>"FSM", "code" => "691"], 
            ["name"=>"Moldova, Republic of",  "shortname"=>"MDA", "code" => "373"], 
            ["name"=>"Monaco",  "shortname"=>"MCO", "code" => "377"], 
            ["name"=>"Mongolia",  "shortname"=>"MNG", "code" => "976"], 
            ["name"=>"Montenegro",  "shortname"=>"MNE", "code" => "382"], 
            ["name"=>"Montserrat",  "shortname"=>"MSR", "code" => "1 664"], 
            ["name"=>"Morocco",  "shortname"=>"MAR", "code" => "212"], 
            ["name"=>"Mozambique",  "shortname"=>"MOZ", "code" => "258"], 
            ["name"=>"Myanmar",  "shortname"=>"MMR", "code" => "95"], 
            ["name"=>"Namibia",  "shortname"=>"NAM", "code" => "264"], 
            ["name"=>"Nauru",  "shortname"=>"NRU", "code" => "674"], 
            ["name"=>"Nepal",  "shortname"=>"NPL", "code" => "977"], 
            ["name"=>"Netherlands",  "shortname"=>"NLD", "code" => "31"], 
            ["name"=>"New Caledonia",  "shortname"=>"NCL", "code" => "687"], 
            ["name"=>"New Zealand",  "shortname"=>"NZL", "code" => "64"], 
            ["name"=>"Nicaragua",  "shortname"=>"NIC", "code" => "505"], 
            ["name"=>"Nigeria",  "shortname"=>"NGA", "code" => "234"], 
            ["name"=>"Niger",  "shortname"=>"NER", "code" => "227"], 
            ["name"=>"Niue",  "shortname"=>"NIU", "code" => "683"], 
            ["name"=>"Norfolk Island",  "shortname"=>"NFK", "code" => "6723"], 
            ["name"=>"Northern Mariana Islands",  "shortname"=>"MNP", "code" => "1 670"], 
            ["name"=>"Norway",  "shortname"=>"NOR", "code" => "47"], 
            ["name"=>"Oman",  "shortname"=>"OMN", "code" => "968"], 
            ["name"=>"Pakistan",  "shortname"=>"PAK", "code" => "92"], 
            ["name"=>"Palau",  "shortname"=>"PLW", "code" => "680"], 
            ["name"=>"Palestinian Territory, Occupied",  "shortname"=>"PSE", "code" => "970"], 
            ["name"=>"Panama",  "shortname"=>"PAN", "code" => "507"], 
            ["name"=>"Papua New Guinea",  "shortname"=>"PNG", "code" => "675"], 
            ["name"=>"Paraguay",  "shortname"=>"PRY", "code" => "595"], 
            ["name"=>"Peru",  "shortname"=>"PER", "code" => "51"], 
            ["name"=>"Philippines",  "shortname"=>"PHL", "code" => "63"], 
            ["name"=>"Pitcairn",  "shortname"=>"PCN", "code" => "64"], 
            ["name"=>"Poland",  "shortname"=>"POL", "code" => "48"], 
            ["name"=>"Portugal",  "shortname"=>"PRT", "code" => "351"], 
            ["name"=>"Puerto Rico",  "shortname"=>"PRI", "code" => "1 57/68"], 
            ["name"=>"Qatar",  "shortname"=>"QAT", "code" => "974"], 
            ["name"=>"Réunion",  "shortname"=>"REU", "code" => "262"], 
            ["name"=>"Romania",  "shortname"=>"ROU", "code" => "40"], 
            ["name"=>"Russian Federation",  "shortname"=>"RUS", "code" => "7"], 
            ["name"=>"Rwanda",  "shortname"=>"RWA", "code" => "250"], 
            ["name"=>"Saint Barthélemy",  "shortname"=>"BLM", "code" => "590"], 
            ["name"=>"Saint Helena",  "shortname"=>"SHN", "code" => "290"], 
            ["name"=>"Saint Kitts and Nevis",  "shortname"=>"KNA", "code" => "1 869"], 
            ["name"=>"Saint Lucia",  "shortname"=>"LCA", "code" => "1 758"], 
            ["name"=>"Saint Martin (French part)",  "shortname"=>"MAF", "code" => "590"], 
            ["name"=>"Saint Pierre and Miquelon",  "shortname"=>"SPM", "code" => "508"], 
            ["name"=>"Saint Vincent and the Grenadines",  "shortname"=>"VCT", "code" => "1 784"], 
            ["name"=>"Samoa",  "shortname"=>"WSM", "code" => "685"], 
            ["name"=>"San Marino",  "shortname"=>"SMR", "code" => "378"], 
            ["name"=>"Sao Tome and Principe",  "shortname"=>"STP", "code" => "239"], 
            ["name"=>"Saudi Arabia",  "shortname"=>"SAU", "code" => "966"], 
            ["name"=>"Senegal",  "shortname"=>"SEN", "code" => "221"], 
            ["name"=>"Serbia",  "shortname"=>"SRB", "code" => "381"], 
            ["name"=>"Seychelles",  "shortname"=>"SYC", "code" => "248"], 
            ["name"=>"Sierra Leone",  "shortname"=>"SLE", "code" => "232"], 
            ["name"=>"Singapore",  "shortname"=>"SGP", "code" => "65"], 
            ["name"=>"Sint Maarten (Dutch part)",  "shortname"=>"SXM", "code" => "1 721"], 
            ["name"=>"Slovakia",  "shortname"=>"SVK", "code" => "421"], 
            ["name"=>"Slovenia",  "shortname"=>"SVN", "code" => "386"], 
            ["name"=>"Solomon Islands",  "shortname"=>"SLB", "code" => "677"], 
            ["name"=>"Somalia",  "shortname"=>"SOM", "code" => "252"], 
            ["name"=>"South Africa",  "shortname"=>"ZAF", "code" => "27"], 
            ["name"=>"South Georgia and the South Sandwich Islands",  "shortname"=>"SGS", "code" => "500"], 
            ["name"=>"South Sudan",  "shortname"=>"SSD", "code" => "211"], 
            ["name"=>"Spain",  "shortname"=>"ESP", "code" => "34"], 
            ["name"=>"Sri Lanka",  "shortname"=>"LKA", "code" => "94"], 
            ["name"=>"Sudan",  "shortname"=>"SDN", "code" => "249"], 
            ["name"=>"Suriname",  "shortname"=>"SUR", "code" => "597"], 
            ["name"=>"Svalbard and Jan Mayen",  "shortname"=>"SJM", "code" => "47"], 
            ["name"=>"Swaziland",  "shortname"=>"SWZ", "code" => "268"], 
            ["name"=>"Sweden",  "shortname"=>"SWE", "code" => "46"], 
            ["name"=>"Switzerland",  "shortname"=>"CHE", "code" => "41"], 
            ["name"=>"Syrian Arab Republic",  "shortname"=>"SYR", "code" => "963"], 
            ["name"=>"Taiwan, Province of China",  "shortname"=>"TWN", "code" => "886"], 
            ["name"=>"Tajikistan",  "shortname"=>"TJK", "code" => "992"], 
            ["name"=>"Tanzania, United Republic of",  "shortname"=>"TZA", "code" => "255"], 
            ["name"=>"Thailand",  "shortname"=>"THA", "code" => "66"], 
            ["name"=>"Timor-Leste",  "shortname"=>"TLS", "code" => "670"], 
            ["name"=>"Togo",  "shortname"=>"TGO", "code" => "228"], 
            ["name"=>"Tokelau",  "shortname"=>"TKL", "code" => "690"], 
            ["name"=>"Tonga",  "shortname"=>"TON", "code" => "676"], 
            ["name"=>"Trinidad and Tobago",  "shortname"=>"TTO", "code" => "1 868"], 
            ["name"=>"Tunisia",  "shortname"=>"TUN", "code" => "216"], 
            ["name"=>"Turkey",  "shortname"=>"TUR", "code" => "90"], 
            ["name"=>"Turkmenistan",  "shortname"=>"TKM", "code" => "993"], 
            ["name"=>"Turks and Caicos Islands",  "shortname"=>"TCA", "code" => "1 649"], 
            ["name"=>"Tuvalu",  "shortname"=>"TUV", "code" => "688"], 
            ["name"=>"Uganda",  "shortname"=>"UGA", "code" => "256"], 
            ["name"=>"Ukraine",  "shortname"=>"UKR", "code" => "380"], 
            ["name"=>"United Arab Emirates",  "shortname"=>"ARE", "code" => "971"], 
            ["name"=>"United Kingdom",  "shortname"=>"GBR", "code" => "44"], 
            ["name"=>"United States Minor Outlying Islands",  "shortname"=>"UMI", "code" => "1 808"], 
            ["name"=>"United States",  "shortname"=>"USA", "code" => "1"], 
            ["name"=>"Uruguay",  "shortname"=>"URY", "code" => "598"], 
            ["name"=>"Uzbekistan",  "shortname"=>"UZB", "code" => "998"], 
            ["name"=>"Vanuatu",  "shortname"=>"VUT", "code" => "678"], 
            ["name"=>"Venezuela",  "shortname"=>"VEN", "code" => "58"], 
            ["name"=>"Vietnam",  "shortname"=>"VNM", "code" => "84"], 
            ["name"=>"Virgin Islands, British",  "shortname"=>"VGB", "code" => "1 284"], 
            ["name"=>"Virgin Islands, U.S.",  "shortname"=>"VIR", "code" => "1 340"], 
            ["name"=>"Wallis and Futuna",  "shortname"=>"WLF", "code" => "681"], 
            ["name"=>"Western Sahara",  "shortname"=>"ESH", "code" => "212 28"], 
            ["name"=>"Yemen",  "shortname"=>"YEM", "code" => "967"], 
            ["name"=>"Zambia",  "shortname"=>"ZMB", "code" => "260"], 
            ["name"=>"Zimbabwe",  "shortname"=>"ZWE", "code" => "263"]
        ];

        foreach($country_list as $country){
            Country::updateOrCreate($country);
        }
        
    }
}