<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class I18nService
{
    /** English string → [lang_code => translation] */
    protected array $translations = [];

    public function __construct()
    {
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
            'Eteya Water Bill' => [
                'orm' => 'Baajii Bishaanii Eteya',
                'am'  => 'የኤትያ የውሃ ሂሳብ',
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
            $text = $this->translations[$key][$lang] ?? $key;
        }

        foreach ($params as $name => $value) {
            $text = str_replace(':'.$name, (string) $value, $text);
        }

        return $text;
    }
}
