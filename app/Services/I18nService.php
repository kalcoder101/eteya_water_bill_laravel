<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use MezgebQalat\Dictionary;

class I18nService
{
    /** English string → [lang_code => translation] */
    protected array $translations = [];

    /** ELRC Ethiopian ICT terminology dictionary (amharic / oromo / tigrigna). */
    protected Dictionary $dictionary;

    public function __construct()
    {
        $this->dictionary = new Dictionary();

        // Compact subset of the original translations table (most-used keys).
        // For brevity only the navigation/login/common strings are listed here.
        $this->translations = [
            'Dashboard' => [
                'orm' => 'Daashboordii',
                'am'  => 'ዳሽቦርድ',
            ],
            'Customer Service' => [
                'orm' => 'Tajaajila Mamiltootaa',
                'am'  => 'የደንበኛ አገልግሎት',
            ],
            'Customers Ledger' => [
                'orm' => 'Kitaaba Hererga Mamiltootaa',
                'am'  => 'የደንበኞች ደብተር',
            ],
            'Detail Statistics' => [
                'orm' => 'BarlagaaBal\'inaa',
                'am'  => 'ዝርዝር ስታቲስቲክስ',
            ],
            'Reading Correction' => [
                'orm' => 'CimfiiQabiyyee',
                'am'  => 'የንባብ ማስተካከያ',
            ],
            'Bills & Printing' => [
                'orm' => 'Baajii fi Maxansaa',
                'am'  => 'ሂሳቦች እና ማተም',
            ],
            'Account Register' => [
                'orm' => 'Galmeessisa Herergaa',
                'am'  => 'ሂሳብ መመዝገቢያ',
            ],
            'Logout' => [
                'orm' => 'Ba\'i',
                'am'  => 'ውጣ',
            ],
            'Login' => [
                'orm' => 'Seeni',
                'am'  => 'ግባ',
            ],
            'SIGN IN' => [
                'orm' => 'Seeni',
                'am'  => 'ግባ',
            ],
            'Sign In' => [
                'orm' => 'Seeni',
                'am'  => 'ግባ',
            ],
            'Username' => [
                'orm' => 'Maqaa Fayyadamaa',
                'am'  => 'የተጠቃሚ ስም',
            ],
            'Password' => [
                'orm' => 'Jechoota-darbee',
                'am'  => 'የይለፍ ቃል',
            ],
            'Incorrect username and password.' => [
                'orm' => 'Maqaa fayyadamaa fi jechoota-darbee dogoggora.',
                'am'  => 'የተጠቃሚ ስም እና የይለፍ ቃል የተሳሳተ ነው።',
            ],
            'WaterSteward Enterprise System' => [
                'orm' => 'Sistiimii Bishaanii WaterSteward',
                'am'  => 'WaterSteward ኢንተርፕራይዝ ሲስተም',
            ],
            'Water Utility Billing System' => [
                'orm' => 'Sistiimii Baajii Bishaanii',
                'am'  => 'የውሃ አገልግሎት የሂሳብ ስርዓት',
            ],
            'Menu' => [
                'orm' => 'Menu',
                'am'  => 'ምናሌ',
            ],
            'Register New Customer' => [
                'orm' => 'Mamila Haaraa Galmeessi',
                'am'  => 'አዲስ ደንበኛ መዝግብ',
            ],
            'Total Customers' => [
                'orm' => 'Mamiltoota Walitti Fufiinsaa',
                'am'  => 'ጠቅላላ ደንበኞች',
            ],
            'Active' => [
                'orm' => 'Socho\'aa',
                'am'  => 'ንቁ',
            ],
            'Disconnected (DC)' => [
                'orm' => 'Cufame (DC)',
                'am'  => 'ተቋርጧል (DC)',
            ],
            'Unpaid Bills' => [
                'orm' => 'Baajii Hin Kaffalamne',
                'am'  => 'ያልተከፈሉ ሂሳቦች',
            ],
            'Pending Complaints' => [
                'orm' => 'Gadda Eegumsa Qabu',
                'am'  => 'በመጠባበቅ ላይ ያሉ ማቅረቦች',
            ],
            'System Users' => [
                'orm' => 'Fayyadamaa Sistimii',
                'am'  => 'የስርዓት ተጠቃሚዎች',
            ],
            'Recent Customers' => [
                'orm' => 'Mamiltoota Dhiyoo',
                'am'  => 'የቅርብ ጊዜ ደንበኞች',
            ],
            'Recent Activity' => [
                'orm' => 'Sochii Dhiyoo',
                'am'  => 'የቅርብ ጊዜ እንቅስቃሴ',
            ],
            'Code' => [
                'orm' => 'Koodii',
                'am'  => 'ኮድ',
            ],
            'Full Name' => [
                'orm' => 'Maqaa Guutuu',
                'am'  => 'ሙሉ ስም',
            ],
            'Kebele' => [
                'orm' => 'Kebele',
                'am'  => 'ቀበሌ',
            ],
            'Type' => [
                'orm' => 'Gosa',
                'am'  => 'አይነት',
            ],
            'Phone' => [
                'orm' => 'Bilbila',
                'am'  => 'ስልክ',
            ],
            'Status' => [
                'orm' => 'Haala',
                'am'  => 'ሁኔታ',
            ],
            'Actions' => [
                'orm' => 'Hojii',
                'am'  => 'ተግባራት',
            ],
            'Edit' => [
                'orm' => 'Gulaali',
                'am'  => 'አስተካክል',
            ],
            'Delete' => [
                'orm' => 'Haqi',
                'am'  => 'ሰርዝ',
            ],
            'Search' => [
                'orm' => 'Barbaadi',
                'am'  => 'ፈልግ',
            ],
            'Calculate Bills' => [
                'orm' => 'Baajii Herereessi',
                'am'  => 'ሂሳቦችን ያስሉ',
            ],
            'Export CSV' => [
                'orm' => 'CSV Baasi',
                'am'  => 'CSV ላክ',
            ],
            'Customer' => [
                'orm' => 'Mamila',
                'am'  => 'ደንበኛ',
            ],
            'Year' => [
                'orm' => 'Waggaa',
                'am'  => 'ዓመት',
            ],
            'Month' => [
                'orm' => 'Ji\'a',
                'am'  => 'ወር',
            ],
            'Submit' => [
                'orm' => 'Ergi',
                'am'  => 'ላክ',
            ],
            'Cancel' => [
                'orm' => 'Dhiisi',
                'am'  => 'ይቅር',
            ],
            'Save' => [
                'orm' => 'Olkaa\'i',
                'am'  => 'አስቀምጥ',
            ],
            'Update' => [
                'orm' => 'Barreeffami',
                'am'  => 'አዘምን',
            ],
            'Register' => [
                'orm' => 'Galmeessi',
                'am'  => 'መዝገብ',
            ],
            'Approve' => [
                'orm' => 'Deggersi',
                'am'  => 'ይደግፉ',
            ],
            'Reject' => [
                'orm' => 'Hambi',
                'am'  => 'ውድቅ',
            ],
            'Pending' => [
                'orm' => 'Eegumsa',
                'am'  => 'በመጠባበቅ ላይ',
            ],
            'Approved' => [
                'orm' => 'Deggarame',
                'am'  => 'የተደገፈ',
            ],
            'Rejected' => [
                'orm' => 'Hambifame',
                'am'  => 'የተውድቀ',
            ],
            'Paid' => [
                'orm' => 'Kaffalame',
                'am'  => 'ተከፈለ',
            ],
            'Unpaid' => [
                'orm' => 'Hin Kaffalamne',
                'am'  => 'ያልተከፈለ',
            ],
            'Total' => [
                'orm' => 'Walitti Fufiinsaa',
                'am'  => 'ድምር',
            ],
            'Bills' => [
                'orm' => 'Baajii',
                'am'  => 'ሂሳቦች',
            ],
            'Complaints' => [
                'orm' => 'Gadda',
                'am'  => 'ቅስቀሴዎች',
            ],
            'All Customers' => [
                'orm' => 'Mamiltoota Hunda',
                'am'  => 'ሁሉም ደንበኞች',
            ],
        ];

        // Tigrigna overrides for app-specific phrases (nav, titles, common UI).
        // General ICT terms fall back to the ELRC Mezgeb Qalat dictionary.
        $this->translations = array_replace_recursive($this->translations, [
            'Operations'            => ['am' => 'ስራዎች', 'orm' => 'Hojiilee', 'ti' => 'ስራሓት'],
            'Reports & Ledger'      => ['am' => 'ሪፖርቶች እና ደብተር', 'orm' => 'Ragaa fi Kitaaba', 'ti' => 'ጸብጻባትን መዝገብን'],
            'Administration'        => ['am' => 'አስተዳደር', 'orm' => 'Maamala', 'ti' => 'ኣስተዳደር'],
            'Dashboard Overview'    => ['am' => 'ዳሽቦርድ አጠቃላይ እይታ', 'orm' => 'Daashboordii Gudunfaa', 'ti' => 'ሓፈሻ ዳሽቦርድ'],
            'Dashboard'             => ['ti' => 'ዳሽቦርድ'],
            'Customer Service'      => ['ti' => 'ኣገልግሎት ደንበኛ'],
            'Customers Ledger'      => ['ti' => 'መዝገብ ደንበኛ'],
            'Detail Statistics'     => ['ti' => 'ዝርዝር ስታቲስቲክስ'],
            'Reading Correction'    => ['ti' => 'ምንባብ ምስተኻኸል'],
            'Bills & Printing'      => ['ti' => 'ሂሳባትን ሕትመትን'],
            'Account Register'      => ['ti' => 'መዝገብ ኣካውንት'],
            'Register New Customer' => ['ti' => 'ሓድሽ ደንበኛ ምዝገባ'],
            'Calculate Bills'       => ['ti' => 'ሂሳባት ምትሕስብ'],
            'Print Report'          => ['ti' => 'ጸብጻብ ሕተም'],
            'Export Ledger CSV'     => ['ti' => 'CSV ወጻኢ'],
            'New Complain'          => ['ti' => 'ሓድሽ ቅሬታ'],
            'Register'              => ['ti' => 'ምዝገባ'],
            'Login'                 => ['ti' => 'እቶ'],
            'Sign In'               => ['ti' => 'እቶ'],
            'SIGN IN'               => ['ti' => 'እቶ'],
            'Username'              => ['ti' => 'ስም ተጠቃሚ'],
            'Password'              => ['ti' => 'መሕለፍቃል'],
            'Logout'                => ['ti' => 'ውጻኢ'],
            'Search'                => ['ti' => 'ድለይ'],
            'Print'                 => ['ti' => 'ሕተም'],
            'Approve'               => ['ti' => 'ኣጽድቕ'],
            'Reject'                => ['ti' => 'ነጸግ'],
            'Pending'               => ['ti' => 'ኣብ ምጽባይ'],
            'Approved'              => ['ti' => 'ኣጽዲቑ'],
            'Rejected'              => ['ti' => 'ነጺጉ'],
            'Paid'                  => ['ti' => 'ተኸፊሉ'],
            'Unpaid'                => ['ti' => 'ዘይተኸፈለ'],
            'Total'                 => ['ti' => 'ጠቕላላ'],
            'Bills'                 => ['ti' => 'ሂሳባት'],
            'Complaints'            => ['ti' => 'ቅሬታታት'],
            'Customer'              => ['ti' => 'ደንበኛ'],
            'Active'                => ['ti' => 'ንጡፍ'],
            'Disconnected (DC)'     => ['ti' => 'ተቖሪጹ (DC)'],
            'Year'                  => ['ti' => 'ዓመት'],
            'Month'                 => ['ti' => 'ወርሒ'],
            'Submit'                => ['ti' => 'ስደድ'],
            'Cancel'                => ['ti' => 'ግደፍ'],
            'Save'                  => ['ti' => 'ኣቐምጥ'],
            'Update'                => ['ti' => 'ምሕዳስ'],
            'Edit'                  => ['ti' => 'ኣርትዕ'],
            'Delete'                => ['ti' => 'ደምስስ'],
            'Actions'               => ['ti' => 'ተግባራት'],
            'Code'                  => ['ti' => 'ኮድ'],
            'Full Name'             => ['ti' => 'ምሉእ ስም'],
            'Kebele'                => ['ti' => 'ቀበሌ'],
            'Type'                  => ['ti' => 'ዓይነት'],
            'Phone'                 => ['ti' => 'ተለፎን'],
            'Status'                => ['ti' => 'ኩነት'],
            'Incorrect username and password.' => ['ti' => 'ስም ተጠቃሚ ወይ መሕለፍቃል ጌጋ እዩ።'],
        ]);
    }

    public function currentLang(): string
    {
        if (request()->has('lang') && array_key_exists(request()->get('lang'), available_languages())) {
            Session::put('lang', request()->get('lang'));
        }
        if (! Session::has('lang') || ! array_key_exists(Session::get('lang'), available_languages())) {
            Session::put('lang', 'en');
        }

        return Session::get('lang');
    }

    public function translate(string $key, array $params = []): string
    {
        $lang = $this->currentLang();
        if ($lang === 'en') {
            $text = $key;
        } else {
            // 1. App-specific override wins (proper names, domain terms).
            // 2. Otherwise fall back to the ELRC Mezgeb Qalat ICT dictionary.
            $text = $this->translations[$key][$lang]
                ?? $this->dictionary->translate($key, $this->dictionaryLocale($lang));
        }

        foreach ($params as $name => $value) {
            $text = str_replace(':'.$name, (string) $value, $text);
        }

        return $text;
    }

    /** Map the app's session lang code to the dictionary's locale names. */
    protected function dictionaryLocale(string $lang): string
    {
        return match ($lang) {
            'am'  => 'amharic',
            'orm' => 'oromo',
            'ti'  => 'tigrigna',
            default => 'english',
        };
    }
}
