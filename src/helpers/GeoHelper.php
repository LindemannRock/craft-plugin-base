<?php
/**
 * LindemannRock Base Module for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\base\helpers;

/**
 * Geo Helper
 *
 * Provides geographic utilities including country code to name conversion.
 * Uses ISO 3166-1 alpha-2 country codes.
 *
 * Usage:
 * ```php
 * use lindemannrock\base\helpers\GeoHelper;
 *
 * $countryName = GeoHelper::getCountryName('US'); // Returns 'United States'
 * $countryName = GeoHelper::getCountryName('GB'); // Returns 'United Kingdom'
 * $countryName = GeoHelper::getCountryName('XX'); // Returns 'XX' (unknown code)
 * ```
 *
 * @author LindemannRock
 * @since 5.3.0
 */
class GeoHelper
{
    /**
     * Phone dial codes mapped to ISO 3166-1 alpha-2 country codes
     * Note: Some dial codes are shared by multiple countries (e.g., +1 for US/CA)
     */
    private const DIAL_CODES = [
        'AD' => '376',
        'AE' => '971',
        'AF' => '93',
        'AG' => '1268',
        'AI' => '1264',
        'AL' => '355',
        'AM' => '374',
        'AO' => '244',
        'AR' => '54',
        'AS' => '1684',
        'AT' => '43',
        'AU' => '61',
        'AW' => '297',
        'AX' => '358',
        'AZ' => '994',
        'BA' => '387',
        'BB' => '1246',
        'BD' => '880',
        'BE' => '32',
        'BF' => '226',
        'BG' => '359',
        'BH' => '973',
        'BI' => '257',
        'BJ' => '229',
        'BL' => '590',
        'BM' => '1441',
        'BN' => '673',
        'BO' => '591',
        'BQ' => '599',
        'BR' => '55',
        'BS' => '1242',
        'BT' => '975',
        'BW' => '267',
        'BY' => '375',
        'BZ' => '501',
        'CA' => '1',
        'CC' => '61',
        'CD' => '243',
        'CF' => '236',
        'CG' => '242',
        'CH' => '41',
        'CI' => '225',
        'CK' => '682',
        'CL' => '56',
        'CM' => '237',
        'CN' => '86',
        'CO' => '57',
        'CR' => '506',
        'CU' => '53',
        'CV' => '238',
        'CW' => '599',
        'CX' => '61',
        'CY' => '357',
        'CZ' => '420',
        'DE' => '49',
        'DJ' => '253',
        'DK' => '45',
        'DM' => '1767',
        'DO' => '1809',
        'DZ' => '213',
        'EC' => '593',
        'EE' => '372',
        'EG' => '20',
        'EH' => '212',
        'ER' => '291',
        'ES' => '34',
        'ET' => '251',
        'FI' => '358',
        'FJ' => '679',
        'FK' => '500',
        'FM' => '691',
        'FO' => '298',
        'FR' => '33',
        'GA' => '241',
        'GB' => '44',
        'GD' => '1473',
        'GE' => '995',
        'GF' => '594',
        'GG' => '44',
        'GH' => '233',
        'GI' => '350',
        'GL' => '299',
        'GM' => '220',
        'GN' => '224',
        'GP' => '590',
        'GQ' => '240',
        'GR' => '30',
        'GT' => '502',
        'GU' => '1671',
        'GW' => '245',
        'GY' => '592',
        'HK' => '852',
        'HN' => '504',
        'HR' => '385',
        'HT' => '509',
        'HU' => '36',
        'ID' => '62',
        'IE' => '353',
        'IL' => '972',
        'IM' => '44',
        'IN' => '91',
        'IO' => '246',
        'IQ' => '964',
        'IR' => '98',
        'IS' => '354',
        'IT' => '39',
        'JE' => '44',
        'JM' => '1876',
        'JO' => '962',
        'JP' => '81',
        'KE' => '254',
        'KG' => '996',
        'KH' => '855',
        'KI' => '686',
        'KM' => '269',
        'KN' => '1869',
        'KP' => '850',
        'KR' => '82',
        'KW' => '965',
        'KY' => '1345',
        'KZ' => '7',
        'LA' => '856',
        'LB' => '961',
        'LC' => '1758',
        'LI' => '423',
        'LK' => '94',
        'LR' => '231',
        'LS' => '266',
        'LT' => '370',
        'LU' => '352',
        'LV' => '371',
        'LY' => '218',
        'MA' => '212',
        'MC' => '377',
        'MD' => '373',
        'ME' => '382',
        'MF' => '590',
        'MG' => '261',
        'MH' => '692',
        'MK' => '389',
        'ML' => '223',
        'MM' => '95',
        'MN' => '976',
        'MO' => '853',
        'MP' => '1670',
        'MQ' => '596',
        'MR' => '222',
        'MS' => '1664',
        'MT' => '356',
        'MU' => '230',
        'MV' => '960',
        'MW' => '265',
        'MX' => '52',
        'MY' => '60',
        'MZ' => '258',
        'NA' => '264',
        'NC' => '687',
        'NE' => '227',
        'NF' => '672',
        'NG' => '234',
        'NI' => '505',
        'NL' => '31',
        'NO' => '47',
        'NP' => '977',
        'NR' => '674',
        'NU' => '683',
        'NZ' => '64',
        'OM' => '968',
        'PA' => '507',
        'PE' => '51',
        'PF' => '689',
        'PG' => '675',
        'PH' => '63',
        'PK' => '92',
        'PL' => '48',
        'PM' => '508',
        'PN' => '64',
        'PR' => '1787',
        'PS' => '970',
        'PT' => '351',
        'PW' => '680',
        'PY' => '595',
        'QA' => '974',
        'RE' => '262',
        'RO' => '40',
        'RS' => '381',
        'RU' => '7',
        'RW' => '250',
        'SA' => '966',
        'SB' => '677',
        'SC' => '248',
        'SD' => '249',
        'SE' => '46',
        'SG' => '65',
        'SH' => '290',
        'SI' => '386',
        'SJ' => '47',
        'SK' => '421',
        'SL' => '232',
        'SM' => '378',
        'SN' => '221',
        'SO' => '252',
        'SR' => '597',
        'SS' => '211',
        'ST' => '239',
        'SV' => '503',
        'SX' => '1721',
        'SY' => '963',
        'SZ' => '268',
        'TC' => '1649',
        'TD' => '235',
        'TG' => '228',
        'TH' => '66',
        'TJ' => '992',
        'TK' => '690',
        'TL' => '670',
        'TM' => '993',
        'TN' => '216',
        'TO' => '676',
        'TR' => '90',
        'TT' => '1868',
        'TV' => '688',
        'TW' => '886',
        'TZ' => '255',
        'UA' => '380',
        'UG' => '256',
        'US' => '1',
        'UY' => '598',
        'UZ' => '998',
        'VA' => '379',
        'VC' => '1784',
        'VE' => '58',
        'VG' => '1284',
        'VI' => '1340',
        'VN' => '84',
        'VU' => '678',
        'WF' => '681',
        'WS' => '685',
        'XK' => '383',
        'YE' => '967',
        'YT' => '262',
        'ZA' => '27',
        'ZM' => '260',
        'ZW' => '263',
    ];

    /**
     * ISO 3166-1 alpha-2 country codes mapped to country names
     */
    private const COUNTRIES = [
        // A
        'AD' => 'Andorra',
        'AE' => 'United Arab Emirates',
        'AF' => 'Afghanistan',
        'AG' => 'Antigua and Barbuda',
        'AI' => 'Anguilla',
        'AL' => 'Albania',
        'AM' => 'Armenia',
        'AO' => 'Angola',
        'AQ' => 'Antarctica',
        'AR' => 'Argentina',
        'AS' => 'American Samoa',
        'AT' => 'Austria',
        'AU' => 'Australia',
        'AW' => 'Aruba',
        'AX' => 'Åland Islands',
        'AZ' => 'Azerbaijan',

        // B
        'BA' => 'Bosnia and Herzegovina',
        'BB' => 'Barbados',
        'BD' => 'Bangladesh',
        'BE' => 'Belgium',
        'BF' => 'Burkina Faso',
        'BG' => 'Bulgaria',
        'BH' => 'Bahrain',
        'BI' => 'Burundi',
        'BJ' => 'Benin',
        'BL' => 'Saint Barthélemy',
        'BM' => 'Bermuda',
        'BN' => 'Brunei',
        'BO' => 'Bolivia',
        'BQ' => 'Caribbean Netherlands',
        'BR' => 'Brazil',
        'BS' => 'Bahamas',
        'BT' => 'Bhutan',
        'BV' => 'Bouvet Island',
        'BW' => 'Botswana',
        'BY' => 'Belarus',
        'BZ' => 'Belize',

        // C
        'CA' => 'Canada',
        'CC' => 'Cocos (Keeling) Islands',
        'CD' => 'Congo (DRC)',
        'CF' => 'Central African Republic',
        'CG' => 'Congo',
        'CH' => 'Switzerland',
        'CI' => 'Côte d\'Ivoire',
        'CK' => 'Cook Islands',
        'CL' => 'Chile',
        'CM' => 'Cameroon',
        'CN' => 'China',
        'CO' => 'Colombia',
        'CR' => 'Costa Rica',
        'CU' => 'Cuba',
        'CV' => 'Cape Verde',
        'CW' => 'Curaçao',
        'CX' => 'Christmas Island',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',

        // D
        'DE' => 'Germany',
        'DJ' => 'Djibouti',
        'DK' => 'Denmark',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'DZ' => 'Algeria',

        // E
        'EC' => 'Ecuador',
        'EE' => 'Estonia',
        'EG' => 'Egypt',
        'EH' => 'Western Sahara',
        'ER' => 'Eritrea',
        'ES' => 'Spain',
        'ET' => 'Ethiopia',

        // F
        'FI' => 'Finland',
        'FJ' => 'Fiji',
        'FK' => 'Falkland Islands',
        'FM' => 'Micronesia',
        'FO' => 'Faroe Islands',
        'FR' => 'France',

        // G
        'GA' => 'Gabon',
        'GB' => 'United Kingdom',
        'GD' => 'Grenada',
        'GE' => 'Georgia',
        'GF' => 'French Guiana',
        'GG' => 'Guernsey',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GL' => 'Greenland',
        'GM' => 'Gambia',
        'GN' => 'Guinea',
        'GP' => 'Guadeloupe',
        'GQ' => 'Equatorial Guinea',
        'GR' => 'Greece',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'GT' => 'Guatemala',
        'GU' => 'Guam',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',

        // H
        'HK' => 'Hong Kong',
        'HM' => 'Heard Island and McDonald Islands',
        'HN' => 'Honduras',
        'HR' => 'Croatia',
        'HT' => 'Haiti',
        'HU' => 'Hungary',

        // I
        'ID' => 'Indonesia',
        'IE' => 'Ireland',
        'IL' => 'Israel',
        'IM' => 'Isle of Man',
        'IN' => 'India',
        'IO' => 'British Indian Ocean Territory',
        'IQ' => 'Iraq',
        'IR' => 'Iran',
        'IS' => 'Iceland',
        'IT' => 'Italy',

        // J
        'JE' => 'Jersey',
        'JM' => 'Jamaica',
        'JO' => 'Jordan',
        'JP' => 'Japan',

        // K
        'KE' => 'Kenya',
        'KG' => 'Kyrgyzstan',
        'KH' => 'Cambodia',
        'KI' => 'Kiribati',
        'KM' => 'Comoros',
        'KN' => 'Saint Kitts and Nevis',
        'KP' => 'North Korea',
        'KR' => 'South Korea',
        'KW' => 'Kuwait',
        'KY' => 'Cayman Islands',
        'KZ' => 'Kazakhstan',

        // L
        'LA' => 'Laos',
        'LB' => 'Lebanon',
        'LC' => 'Saint Lucia',
        'LI' => 'Liechtenstein',
        'LK' => 'Sri Lanka',
        'LR' => 'Liberia',
        'LS' => 'Lesotho',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'LV' => 'Latvia',
        'LY' => 'Libya',

        // M
        'MA' => 'Morocco',
        'MC' => 'Monaco',
        'MD' => 'Moldova',
        'ME' => 'Montenegro',
        'MF' => 'Saint Martin',
        'MG' => 'Madagascar',
        'MH' => 'Marshall Islands',
        'MK' => 'North Macedonia',
        'ML' => 'Mali',
        'MM' => 'Myanmar',
        'MN' => 'Mongolia',
        'MO' => 'Macao',
        'MP' => 'Northern Mariana Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MS' => 'Montserrat',
        'MT' => 'Malta',
        'MU' => 'Mauritius',
        'MV' => 'Maldives',
        'MW' => 'Malawi',
        'MX' => 'Mexico',
        'MY' => 'Malaysia',
        'MZ' => 'Mozambique',

        // N
        'NA' => 'Namibia',
        'NC' => 'New Caledonia',
        'NE' => 'Niger',
        'NF' => 'Norfolk Island',
        'NG' => 'Nigeria',
        'NI' => 'Nicaragua',
        'NL' => 'Netherlands',
        'NO' => 'Norway',
        'NP' => 'Nepal',
        'NR' => 'Nauru',
        'NU' => 'Niue',
        'NZ' => 'New Zealand',

        // O
        'OM' => 'Oman',

        // P
        'PA' => 'Panama',
        'PE' => 'Peru',
        'PF' => 'French Polynesia',
        'PG' => 'Papua New Guinea',
        'PH' => 'Philippines',
        'PK' => 'Pakistan',
        'PL' => 'Poland',
        'PM' => 'Saint Pierre and Miquelon',
        'PN' => 'Pitcairn Islands',
        'PR' => 'Puerto Rico',
        'PS' => 'Palestine',
        'PT' => 'Portugal',
        'PW' => 'Palau',
        'PY' => 'Paraguay',

        // Q
        'QA' => 'Qatar',

        // R
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RS' => 'Serbia',
        'RU' => 'Russia',
        'RW' => 'Rwanda',

        // S
        'SA' => 'Saudi Arabia',
        'SB' => 'Solomon Islands',
        'SC' => 'Seychelles',
        'SD' => 'Sudan',
        'SE' => 'Sweden',
        'SG' => 'Singapore',
        'SH' => 'Saint Helena',
        'SI' => 'Slovenia',
        'SJ' => 'Svalbard and Jan Mayen',
        'SK' => 'Slovakia',
        'SL' => 'Sierra Leone',
        'SM' => 'San Marino',
        'SN' => 'Senegal',
        'SO' => 'Somalia',
        'SR' => 'Suriname',
        'SS' => 'South Sudan',
        'ST' => 'São Tomé and Príncipe',
        'SV' => 'El Salvador',
        'SX' => 'Sint Maarten',
        'SY' => 'Syria',
        'SZ' => 'Eswatini',

        // T
        'TC' => 'Turks and Caicos Islands',
        'TD' => 'Chad',
        'TF' => 'French Southern Territories',
        'TG' => 'Togo',
        'TH' => 'Thailand',
        'TJ' => 'Tajikistan',
        'TK' => 'Tokelau',
        'TL' => 'Timor-Leste',
        'TM' => 'Turkmenistan',
        'TN' => 'Tunisia',
        'TO' => 'Tonga',
        'TR' => 'Turkey',
        'TT' => 'Trinidad and Tobago',
        'TV' => 'Tuvalu',
        'TW' => 'Taiwan',
        'TZ' => 'Tanzania',

        // U
        'UA' => 'Ukraine',
        'UG' => 'Uganda',
        'UM' => 'U.S. Minor Outlying Islands',
        'US' => 'United States',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',

        // V
        'VA' => 'Vatican City',
        'VC' => 'Saint Vincent and the Grenadines',
        'VE' => 'Venezuela',
        'VG' => 'British Virgin Islands',
        'VI' => 'U.S. Virgin Islands',
        'VN' => 'Vietnam',
        'VU' => 'Vanuatu',

        // W
        'WF' => 'Wallis and Futuna',
        'WS' => 'Samoa',

        // X
        'XK' => 'Kosovo',

        // Y
        'YE' => 'Yemen',
        'YT' => 'Mayotte',

        // Z
        'ZA' => 'South Africa',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
    ];

    /**
     * Get country name from ISO 3166-1 alpha-2 code
     *
     * @param string $countryCode Two-letter country code (e.g., 'US', 'GB')
     * @return string Country name, or the original code if not found
     * @since 5.3.0
     */
    public static function getCountryName(string $countryCode): string
    {
        if (empty($countryCode)) {
            return '';
        }

        $code = strtoupper(trim($countryCode));

        return self::COUNTRIES[$code] ?? $code;
    }

    /**
     * Get all countries as an array
     *
     * @return array<string, string> Country codes mapped to names
     * @since 5.3.0
     */
    public static function getAllCountries(): array
    {
        return self::COUNTRIES;
    }

    /**
     * Check if a country code is valid
     *
     * @param string $countryCode Two-letter country code
     * @return bool True if valid ISO 3166-1 alpha-2 code
     * @since 5.3.0
     */
    public static function isValidCountryCode(string $countryCode): bool
    {
        if (empty($countryCode)) {
            return false;
        }

        $code = strtoupper(trim($countryCode));

        return isset(self::COUNTRIES[$code]);
    }

    /**
     * Get phone dial code from ISO 3166-1 alpha-2 country code
     *
     * @param string $countryCode Two-letter country code (e.g., 'KW', 'US')
     * @return string|null Dial code without + (e.g., '965'), or null if not found
     * @since 5.7.0
     */
    public static function getDialCode(string $countryCode): ?string
    {
        if (empty($countryCode)) {
            return null;
        }

        $code = strtoupper(trim($countryCode));

        return self::DIAL_CODES[$code] ?? null;
    }

    /**
     * Get all dial codes as an array
     *
     * @return array<string, string> Country codes mapped to dial codes
     * @since 5.7.0
     */
    public static function getAllDialCodes(): array
    {
        return self::DIAL_CODES;
    }

    /**
     * Get countries with their dial codes for select options
     *
     * @param bool $includeAll Whether to include an "All Countries" option at the top
     * @return array<array{label: string, value: string}> Array of options with label "Country Name (+code)" and value "XX"
     * @since 5.7.0
     */
    public static function getCountryDialCodeOptions(bool $includeAll = false): array
    {
        $options = [];

        // Add "All Countries" option if requested
        if ($includeAll) {
            $options[] = [
                'label' => 'All Countries',
                'value' => '*',
            ];
        }

        $countryOptions = [];
        foreach (self::COUNTRIES as $code => $name) {
            $dialCode = self::DIAL_CODES[$code] ?? null;
            if ($dialCode) {
                $countryOptions[] = [
                    'label' => $name . ' (+' . $dialCode . ')',
                    'value' => $code,
                ];
            }
        }

        // Sort by label
        usort($countryOptions, fn($a, $b) => strcmp($a['label'], $b['label']));

        return array_merge($options, $countryOptions);
    }

    /**
     * Check if a phone number matches any of the allowed country codes
     *
     * @param string $phoneNumber Phone number (with or without +, just digits)
     * @param array<string> $allowedCountryCodes Array of ISO 3166-1 alpha-2 codes (e.g., ['KW', 'SA']) or ['*'] for all
     * @return bool True if phone number starts with one of the allowed dial codes
     * @since 5.7.0
     */
    public static function isPhoneNumberAllowed(string $phoneNumber, array $allowedCountryCodes): bool
    {
        if (empty($allowedCountryCodes)) {
            // No restrictions
            return true;
        }

        // Check for "All Countries" wildcard
        if (in_array('*', $allowedCountryCodes, true)) {
            return true;
        }

        // Normalize phone number - remove everything except digits
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (empty($phoneNumber)) {
            return false;
        }

        // Get dial codes for allowed countries, sorted by length descending (longest first)
        $allowedDialCodes = [];
        foreach ($allowedCountryCodes as $countryCode) {
            $dialCode = self::getDialCode($countryCode);
            if ($dialCode) {
                $allowedDialCodes[] = $dialCode;
            }
        }

        // Sort by length descending to match longer codes first (e.g., 1242 before 1)
        usort($allowedDialCodes, fn($a, $b) => strlen($b) - strlen($a));

        // Check if phone number starts with any allowed dial code
        foreach ($allowedDialCodes as $dialCode) {
            if (str_starts_with($phoneNumber, $dialCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get country name and dial code for display
     *
     * @param string $countryCode Two-letter country code
     * @return string Formatted string like "Kuwait (+965)" or empty if not found
     * @since 5.7.0
     */
    public static function getCountryWithDialCode(string $countryCode): string
    {
        $name = self::getCountryName($countryCode);
        $dialCode = self::getDialCode($countryCode);

        if ($name === $countryCode || !$dialCode) {
            return '';
        }

        return $name . ' (+' . $dialCode . ')';
    }
}
