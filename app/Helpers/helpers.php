<?php
/*
=> Register File in composer.json

"autoload-dev": {
    ...
    "files": [
        "app/Helpers/helpers.php",
    ]
},

=> Run command: composer dump-autoload
*/

// function to get  the address
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

if (!function_exists('app_name')) {
    function app_name()
    {
        return config('app.name');
    }
}
if (!function_exists('app_full_name')) {
    function app_full_name()
    {
        return config('app.full_name');
    }
}
if (!function_exists('is_production')) {
    function is_production()
    {
        return in_array(strtolower(config('app.env')), ['prod', 'production']);
    }
}
if (!function_exists('is_staging')) {
    function is_staging()
    {
        return in_array(strtolower(config('app.env')), ['dev', 'development', 'stg', 'staging']);
    }
}
if (!function_exists('is_local')) {
    function is_local()
    {
        return in_array(strtolower(config('app.env')), ['local']);
    }
}
if (!function_exists('is_testing')) {
    function is_testing()
    {
        return in_array(strtolower(config('app.env')), ['test', 'testing']);
    }
}
if (!function_exists('is_debug_mode')) {
    function is_debug_mode()
    {
        return config('app.debug');
    }
}
if (!function_exists('app_url')) {
    function app_url()
    {
        return config('app.url');
    }
}
if (!function_exists('app_asset_url')) {
    function app_asset_url()
    {
        return config('app.asset_url');
    }
}
if (!function_exists('app_timezone')) {
    function app_timezone()
    {
        return config('app.timezone');
    }
}
if (!function_exists('app_local')) {
    function app_local()
    {
        return config('app.locale');
    }
}


if (!function_exists('remaining_days_of_month')) {
    function remaining_days_of_month($date = null, $useEndOfDateMonth = false)
    {
        try {
            $date = Carbon::parse($date, app_timezone());
            $endOfMonth = $useEndOfDateMonth
                ? Carbon::parse($date, app_timezone())->endOfMonth()
                : Carbon::now(app_timezone())->endOfMonth();

            if ($date->gt($endOfMonth)) return -1;

            return isset($date)
                ? $date->diffInDays($endOfMonth)
                : Carbon::now(app_timezone())->diffInDays($endOfMonth);
        } catch (Exception $exception) {
            return -1;
        }
    }
}

if (!function_exists('remaining_days_till')) {
    function remaining_days_till($dateTo, $dateFrom = null)
    {
        try {
            $dateTo = Carbon::parse($dateTo, app_timezone());
            $dateFrom = isset($dateFrom)
                ? Carbon::parse($dateFrom, app_timezone())
                : Carbon::now(app_timezone());

            if ($dateFrom->gt($dateTo)) return -1;

            return $dateFrom->diffInDays($dateTo);
        } catch (Exception $exception) {
            return -1;
        }
    }
}

if (!function_exists('days_in_month')) {
    function days_in_month($date = null)
    {
        try {
            return isset($date)
                ? Carbon::parse($date, app_timezone())->daysInMonth
                : Carbon::now(app_timezone())->daysInMonth;
        } catch (Exception $exception) {
            return -1;
        }
    }
}

if (!function_exists('html_symbols')) {
    function html_symbols($name)
    {
        try {
            $code = '';
            switch (strtolower($name)) {
                case 'arrow_top': $code = '↑'; break;
                case 'arrow_left': $code = '←'; break;
                case 'arrow_right': $code = '→'; break;
                case 'arrow_bottom': $code = '↓'; break;
                case 'arrow_top_left': $code = '↖'; break;
                case 'arrow_top_right': $code = '↗'; break;
                case 'arrow_bottom_left': $code = '↙'; break;
                case 'arrow_bottom_right': $code = '↘'; break;

                case 'copyright': $code = '©'; break;
                case 'registered': $code = '®'; break;
                case 'trademark': $code = '™'; break;
                case '@': case 'at': $code = '@'; break;
                case '&': case 'ampersand': $code = '&'; break;
                case 'check': $code = '✓'; break;
                case 'celsius': $code = '℃'; break;
                case 'fahrenheit': $code = '℉'; break;

                case 'dollar': $code = '$'; break;
                case 'cent': $code = '¢'; break;
                case 'pound': $code = '£'; break;
                case 'euro': $code = '€'; break;
                case 'yen': $code = '¥'; break;
                case 'indian': $code = '₹'; break;
                case 'ruble': $code = '₽'; break;
                case 'yuan': $code = '元'; break;

                case '+': case 'plus': case 'add': $code = '+'; break;
                case '-': case 'minus': case 'subtract': case 'dash': case 'en': $code = '−'; break;
                case '*': case 'asterisk': case 'multiply': $code = '×'; break;
                case '/': case 'division': case 'divide': case 'forward_slash': $code = '÷'; break;
                case '=': case 'equal': $code = '='; break;
                case '!=': case 'notequal': $code = '≠'; break;
                case '<': case 'lessthan': $code = '<'; break;
                case '>': case 'greaterthan': $code = '>'; break;

                case '!': case 'exclamation': $code = '!'; break;
                case '?': case 'question': $code = '?'; break;
                case '--': case 'em': case 'doubledash': $code = '—'; break;
                case 'singleleft': $code = '‹'; break;
                case 'singleright': $code = '›'; break;
                case 'doubleleft': $code = '«'; break;
                case 'doubleright': $code = '»'; break;

                default: $code = '';
            }

            return $code;
        } catch (Exception $exception) {
            return '';
        }
    }
}

if (!function_exists('html_symbol_codes')) {
    function html_symbol_codes($name)
    {
        try {
            $code = '';
            switch (strtolower($name)) {
                case 'arrow_top': $code = '&#8593;'; break;
                case 'arrow_left': $code = '&#8592;'; break;
                case 'arrow_right': $code = '&#8594;'; break;
                case 'arrow_bottom': $code = '&#8595;'; break;
                case 'arrow_top_left': $code = '&#8598;'; break;
                case 'arrow_top_right': $code = '&#8599;'; break;
                case 'arrow_bottom_left': $code = '&#8601;'; break;
                case 'arrow_bottom_right': $code = '&#8600;'; break;

                case 'copyright': $code = '&#169;'; break;
                case 'registered': $code = '&#174;'; break;
                case 'trademark': $code = '&#8482;'; break;
                case '@': case 'at': $code = '&#64;'; break;
                case '&': case 'ampersand': $code = '&#38;'; break;
                case 'check': $code = '&#10003;'; break;
                case 'celsius': $code = '&#8451;'; break;
                case 'fahrenheit': $code = '&#8457;'; break;

                case 'dollar': $code = '&#36;'; break;
                case 'cent': $code = '&#162;'; break;
                case 'pound': $code = '&#163;'; break;
                case 'euro': $code = '&#8364;'; break;
                case 'yen': $code = '&#165;'; break;
                case 'indian': $code = '&#8377;'; break;
                case 'ruble': $code = '&#8381;'; break;
                case 'yuan': $code = '&#20803;'; break;

                case '+': case 'plus': case 'add': $code = '&#43;'; break;
                case '-': case 'minus': case 'subtract': case 'dash': case 'en': $code = '&#8722;'; break;
                case '*': case 'asterisk': case 'multiply': $code = '&#215;'; break;
                case '/': case 'division': case 'divide': case 'forward_slash': $code = '&#247;'; break;
                case '=': case 'equal': $code = '&#61;'; break;
                case '!=': case 'notequal': $code = '&#8800;'; break;
                case '<': case 'lessthan': $code = '&#60;'; break;
                case '>': case 'greaterthan': $code = '&#62;'; break;

                case '!': case 'exclamation': $code = '&#33;'; break;
                case '?': case 'question': $code = '&#63;'; break;
                case '--': case 'em': case 'doubledash': $code = '&#8212;'; break;
                case 'singleleft': $code = '&#8249;'; break;
                case 'singleright': $code = '&#8250;'; break;
                case 'doubleleft': $code = '&#171;'; break;
                case 'doubleright': $code = '&#187;'; break;

                default: $code = '';
            }

            return $code;
        } catch (Exception $exception) {
            return '';
        }
    }
}

if (!function_exists('exception_response')) {
    function exception_response($exception)
    {
        try {
            if ($exception instanceof Exception) {
                $exception = [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile() . ' : ' . $exception->getLine(),
                    'code' => $exception->getCode()
                ];
                return request()->isJson() || request()->wantsJson()
                    ? response()->json($exception, $exception['code'])
                    : $exception;
            }
            return $exception;
        } catch (Exception $exception) {
            return $exception;
        }
    }
}

if (!function_exists('json_to_xml')) {
    function json_to_xml($json, $useFirstKeyAsRootTag = false, $path = null)
    {
        try {
            $array = json_decode($json, true);
            return array_to_xml($array, $useFirstKeyAsRootTag, $path);
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('array_to_xml')) {
    function array_to_xml($array, $useFirstKeyAsRootTag = false, $path = null)
    {
        try {
            $root = $useFirstKeyAsRootTag ? array_key_first($array) : 'root';
            $array = $useFirstKeyAsRootTag ? $array[$root] : $array;

            $simpleXmlElement = new \SimpleXMLElement(sprintf("<?xml version=\"1.0\"?><%s></%s>", $root, $root));
            array_to_xml_conversion_script($array, $simpleXmlElement);
            return $result = isset($path) ? $simpleXmlElement->asXML($path) : $simpleXmlElement->asXML();
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('xml_to_array')) {
    function xml_to_array($xml, $wrap = null)
    {
        try {
            $xml = simplexml_load_string($xml);
            $jsonConvert = json_encode($xml);
            $jsonConvert = json_decode($jsonConvert, true);
            if (isset($wrap)) $finalJson[$wrap] = $jsonConvert;
            else $finalJson = $jsonConvert;
            return $finalJson;
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('xml_to_json')) {
    function xml_to_json($xml, $wrap = null)
    {
        try {
            return json_encode(xml_to_array($xml, $wrap));
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('array_to_xml_conversion_script')) {
    function array_to_xml_conversion_script($array, &$simpleXmlElement)
    {
        try {
            foreach ($array as $key => $value) {
                if (!is_array($value)) {
                    $simpleXmlElement->addChild("$key", "$value");
                    continue;
                }

                if (is_numeric($key)) {
                    array_to_xml_conversion_script($value, $simpleXmlElement);
                    continue;
                }

                $isAssoc = Arr::isAssoc($value);
                if ($isAssoc) {
                    $subnode = $simpleXmlElement->addChild("$key");
                    array_to_xml_conversion_script($value, $subnode);
                    continue;
                }

                $jump  = false;
                foreach ($value as $k => $v) {
                    $key = is_numeric($k) ? $key : $k;
                    if (is_array($v)) {
                        $subnode = $simpleXmlElement->addChild("$key");
                        array_to_xml_conversion_script($v, $subnode);
                        $jump = true;
                    }
                }

                if ($jump) continue;
                array_to_xml_conversion_script($value, $subnode);
            }
            return null;
        } catch (Exception $exception) {
            return null;
        }
    }
}






if (!function_exists('send_fcm_notification')) {
    function send_fcm_notification($deviceToken, $body)
    {
        $accessToken = config('firebase.cloud_messaging.fcm_token');
//        $accessToken = 'AAAA7LoOfDE:APA91bEkfqyxcZo9zb5e9gwD1jYznDR78F1nOLHp5r4jDn6zmCwD9JjIzNe5y8GL7Y9C53KrBDM2H85DV5yhJTWMienNSGNUv1l7aenrxARRtJ72GvZI_E5bRsCJx2dRZQAARexTKWT8';
        $URL = 'https://fcm.googleapis.com/fcm/send';
        $post_data = '{
           "notification":{
              "title":"' . 'Pantheon Notification' . '",
              "body":"' . $body . '",
              "image":"",
              "sound":"default",
              "android_channel_id":"fcm_default_channel"
           },
           "priority":"high",
           "data":{
              "click_action":"FLUTTER_NOTIFICATION_CLICK",
           },
           "android":{
              "priority":"high",
              "notification":{
                 "title":"' . 'Pantheon Notification' . '",
                 "body":"' . $body . '",
                 "sound":"default"
              }
           },
           "apns":{
              "aps":{
                 "alert":{
                    "title":"' . 'Pantheon Notification' . '",
                    "body":"' . $body . '"
                 },
                 "badge":1
              },
              "headers":{
                 "apns-priority":10
              },
              "payload":{
                 "aps":{
                    "sound":"default"
                 }
              },
              "fcm_options":{
                 "image":""
              },
              "customKey":"customValue"
           },
           "time_to_live":3600,
           "to":"' . $deviceToken . '"
        }';

        $crl = curl_init();

        $headers = array();
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Authorization: key=' . $accessToken;
        curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($crl, CURLOPT_URL, $URL);
        curl_setopt($crl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($crl, CURLOPT_POST, true);
        curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($crl);
        curl_close($crl);

        logs()->info('fcm:result::'.'Pantheon Notification', [
            'rest' => json_decode($result),
            'notification' => [
                'title' => 'Pantheon Notification',
                'message' => $body,
                'post_data' => '
                    "data":{
                      "click_action":"FLUTTER_NOTIFICATION_CLICK",
                   }
                ',
            ],
            'device_token' => $deviceToken,
        ]);
//dd($result);
        return $result;
    }
}

if (!function_exists('get_lat_lng_from_address')) {
    function get_lat_lng_from_address($address)
    {
        $apiKey = config('services.google.map.api_key');

        try {
            $address = str_replace(" ", "+", $address);

            // $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&key='.$apiKey&sensor=false"); // &region=$region
            $json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey"); // &region=$region
            $json = json_decode($json);

            if (isset($json->{'results'}) && count($json->{'results'}) > 0) {
                $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
                return ['lat' => $lat, 'lng' => $long];
            }

            return null;
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('generate_unique_id')) {
    function generate_unique_id($length = 10)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}

if (!function_exists('generate_unique_id_model')) {
    function generate_unique_id_model($modal, $column, $uniqueIdPrefix = '', $length = 10, $recursive = 5)
    {
        $uniqueId = generate_unique_id($length);
        if ($recursive != 0) {
            if ($modal::where($column, '=', $uniqueIdPrefix . $uniqueId)->exists()) $uniqueId = generate_unique_id_model($length, ($recursive - 1));
        } else {
            $count = $modal::where($column, 'LIKE', '%' . $uniqueId . '%')->count();
            $uniqueId = $uniqueId . $count;
        }
        return $uniqueId;
    }
}


if (!function_exists('secret_value')) {
    function secret_value(string $string, $display = [4, -4], bool $displayBetween = false, $char = '*')
    {
        $length = strlen($string);

        if ($displayBetween) {
            $mask_number = str_repeat($char, abs($display[0])) . substr($string, abs($display[0]), $display[1]) . str_repeat($char, abs($display[1]));
        } else {
            $lengthHidden = $length - (abs($display[0]) + abs($display[1]));
            $mask_number = substr($string, 0, abs($display[0])) . str_repeat($char, $lengthHidden) . substr($string, abs($display[0]) + $lengthHidden);
        }

        return $mask_number;
    }
}

if (!function_exists('is_zero')) {
    function is_zero($number)
    {
        return $number == 0;
    }
}

if (!function_exists('is_negative')) {
    function is_negative($number)
    {
        return $number < 0;
    }
}

if (!function_exists('is_negative_or_zero')) {
    function is_negative_or_zero($number)
    {
        return $number <= 0;
    }
}

if (!function_exists('is_positive')) {
    function is_positive($number)
    {
        return $number > 0;
    }
}

if (!function_exists('is_positive_or_zero')) {
    function is_positive_or_zero($number)
    {
        return $number >= 0;
    }
}

if (!function_exists('filesystems_setup')) {
    function filesystems_setup($shared = false, $sharedPath = null): array
    {
        $disks = [];
        $shared = !isset($sharedPath) ? false : $shared;

        if ($shared) {
            $links[public_path('media/public')] = "$sharedPath/public";
//            $links["$sharedPath/public"] = storage_path('app/public');
//            $links[public_path('media/public')] = $links["$sharedPath/public"];
        } else {
            $links = [public_path('media/public') => storage_path('app/public')];
        }

        foreach (\App\Models\Media::DISKS as $key => $value) {
            $disks[$key] = [
                'driver' => 'local',
                'root' => storage_path("app/{$key}"),
                'url' => trim(app_url(), '/') . "/media/{$value}",
                'visibility' => 'public',
            ];

            if ($shared) {
                $links[public_path('media/' . $key)] = "$sharedPath/$key";
//                $links["$sharedPath/$key"] = storage_path('app/' . $value);
//                $links[public_path('media/' . $key)] = $links["$sharedPath/$key"];
            } else {
                $links[public_path('media/' . $key)] = storage_path('app/' . $value);
            }
        }

        return [
            'disks' => $disks,
            'links' => $links,
        ];
    }
}


if (!function_exists('get_morphs_maps')) {
    function get_morphs_maps($class = null)
    {
        $maps = [
            'user'                 => \App\Models\User::class,
        ];

        if (isset($class)) {
            return array_search($class, $maps);
        }

        return $maps;
    }
}

if (!function_exists('avatar_name')) {
    function avatar_name($string, $delimiter = ' ', $uppercase = true, $limit = 2)
    {
        return words_fc($string, $delimiter, $uppercase, $limit);
    }
}

if (!function_exists('get_gravatar')) {
    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param string $email The email address
     * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar | retro | robohash | blank ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boole $img True to return a complete IMG tag False for just the URL
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return String containing either just a URL or a complete image tag
     * @source https://gravatar.com/site/implement/images/php/
     */
    function get_gravatar($email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array())
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val)
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }
}

if (!function_exists('words_fc')) {
    function words_fc($string, $delimiter = ' ', $uppercase = true, $limit = 0)
    {
        $words = explode($delimiter, trim($string));

        $limit = $limit > 0 ? $limit : count($words);
        $limit = count($words) < $limit ? count($words) : $limit;

        $acronym = '';
        for ($i = 0; $i < $limit; $i++) {
            $acronym .= $words[$i][0];
        }

        return $uppercase ? strtoupper($acronym) : strtolower($acronym);
    }
}

if (!function_exists('get_date_periods_between')) {
    function get_date_periods_between($startDate, $endDate = null, $format = 'd M')
    {
        $endDate = isset($endDate) ? $endDate : now();

        $periods = CarbonPeriod::create($startDate, $endDate);
        $datePeriods = [];
        foreach ($periods as $date) {
            $datePeriods[] = $date->format($format);
        }
        return $datePeriods;
    }
}

if (!function_exists('get_percentage_of_amount')) {
    function get_percentage_of_amount($current, $total)
    {
        if ($total == 0) return 0;
        return ($current / $total) * 100;
    }
}

if (!function_exists('get_amount_of_percentage')) {
    function get_amount_of_percentage($percentage, $totalAmount)
    {
        if ($percentage == 0) return 0;
        return ($percentage / 100) * $totalAmount;
    }
}

if (!function_exists('is_type_image')) {
    function is_type_image(string $string)
    {
        try {
            $is_image = false;
            if (strpos($string, ' image/') !== false) {
                $is_image = true;
            }
            if (in_array(strtolower($string), ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'svg', 'webp'])) {
                $is_image = true;
            }

            return $is_image;
        } catch (Exception $exception) {
            return false;
        }
    }
}
if (!function_exists('is_type_document')) {
    function is_type_document($string)
    {
        if (in_array($string, [])) {

        }
    }
}
if (!function_exists('media_is_type_of')) {
    function media_is_type_of($string)
    {
        if (in_array($string, [])) {

        }
    }
}
if (!function_exists('carbon_datetime_between_included')) {
    function carbon_datetime_between_included($start, $end, $date = null)
    {
        $date = \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i');

        $startDate = \Carbon\Carbon::parse($start)->format('Y-m-d H:i:s');
        $endDate = \Carbon\Carbon::parse($end)->format('Y-m-d H:i:s');
        $check = (new \Carbon\Carbon($date))->betweenIncluded($startDate, $endDate);
        return $check;
    }
}
if (!function_exists('carbon_datetime_between_excluded')) {
    function carbon_datetime_between_excluded($start, $end, $date = null)
    {
        $date = \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i');

        $startDate = \Carbon\Carbon::parse($start)->format('Y-m-d H:i:s');
        $endDate = \Carbon\Carbon::parse($end)->format('Y-m-d H:i:s');
        $check = (new \Carbon\Carbon($date))->betweenExcluded($startDate, $endDate);
        return $check;
    }
}
if (!function_exists('random_color_hex_part')) {
    function random_color_hex_part()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }
}
if (!function_exists('generate_random_color_hex')) {
    function generate_random_color_hex()
    {
        return '#' . random_color_hex_part() . random_color_hex_part() . random_color_hex_part();
    }
}
if (!function_exists('number_to_words')) {
    function number_to_words($number, $isApprox = false)
    {
        try {
            $number = str_replace(',', '', $number);

            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $spell = $formatter->format($number);
            $spell = strtolower($spell);

            if ($isApprox) {
                $spells = explode(' ', $spell);
                if ($spells[1] == 'hundred') {
                    $spell = $spells[0] . ' ' . $spells[1] . ' ' . $spells[2];
                } else {
                    $spell = $spells[0] . ' ' . $spells[1];
                }
            }

            return $spell;
        } catch (Exception $exception) {
            return 'zero';
        }
    }
}

if (!function_exists('snake_case')) {
    function snake_case($string) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}

function routeCurrentName() {
//    return Route::getCurrentRoute()->getName();
    return Request::url();
}

function getRouteCurrentName() {
    return request()->segment(count(request()->segments()));
}

function routeIsActive($name, $activeClass = "active") {
    return routeCurrentName() == $name ? $activeClass : '';
}

if (!function_exists('array_flatten')) {
    function array_flatten($array)
    {
        if (!is_array($array)) return false;

        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) $result = array_merge($result, array_flatten($value));
            else $result[] = $value;
        }

        return $result;
    }
}
if (!function_exists('logout_auth_user')) {
    function logout_auth_user($request = null)
    {
        if (!auth_check()) return redirect()->route('index');
        $redirect = (new \App\Http\Controllers\Auth\LoginController())->logout($request ?? request());
        return $redirect;
    }
}

if (!function_exists('get_complete_image_url')) {
    function get_complete_image_url($data = null)
    {
        try {
            return filter_var($data, FILTER_VALIDATE_URL) ? $data : trim(app_url(), '/') . '/' . trim($data, '/');
        } catch (Exception $exception) {

        }
    }
}


if (!function_exists('is_available')) {
    function is_available($string)
    {
        try {
            $is_available = false;
            if (isset($string) && $string != '' && $string != null) {
                $is_available = true;
            }
            return $is_available;
        } catch (Exception $exception) {
            return false;
        }
    }
}

static $sidebarView = null;

if (!function_exists('get_sidebar_view_by_user')) {
    /**
     * Get the sidebar view file path based on the user's role/level.
     *
     * @param User|null $user
     * @return string|null
     */
    function get_sidebar_view_by_user(User $user = null): ?string
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        if (!$user && auth()->check()) {
            $user = auth()->user();
        }

        if (!$user) {
            return null;
        }

        $map = [
            User::LEVEL_SUPER_ADMIN => 'includes.navigations._inc_side_bar._super_admin_side_bar',
            User::LEVEL_ADMIN       => 'includes.navigations._inc_side_bar._admin_side_bar',
            User::LEVEL_MANAGER     => 'includes.navigations._inc_side_bar._manager_side_bar',
            User::LEVEL_CUSTOMER    => 'includes.navigations._inc_side_bar._customer_side_bar',
        ];

        return $cached = $map[$user->level] ?? null;
    }
}

//if (!function_exists('generate_slug')) {
//    /**
//     * Generate a URL-friendly slug with optional prefix and uniqueness.
//     *
//     * @param string $base Optional base string (e.g. name or title)
//     * @param int $length Length of the random suffix (ignored if $unique = false)
//     * @param string $prefix Optional prefix to prepend (e.g. 'user-', 'post-')
//     * @param bool $unique Whether to append a random string for uniqueness
//     * @return string
//     */
//    function generate_slug(string $base = '', int $length = 32, string $prefix = '', bool $unique = true): string
//    {
//        $slug = strtolower(trim($base));
//        $slug = preg_replace('/[^a-z0-9\-_]/', '-', $slug);
//        $slug = preg_replace('/-+/', '-', $slug);
//        $slug = trim($slug, '-');
//
//        $slug = Str::slug($base);
//
//        if ($unique) {
//            $slug .= '-' . Str::lower(Str::random($length));
//        }
//
//        // Truncate to max length
//        if (strlen($slug) > $length) {
//            $slug = substr($slug, 0, $length);
//            // Remove trailing hyphen if truncation creates one
//            $slug = rtrim($slug, '-');
//        }
//
//        return $prefix . $slug;
//    }
//}

if (!function_exists('generate_secure_key')) {
    /**
     * Generate a cryptographically secure random string.
     *
     * @param int $length Desired string length (must be even).
     * @return string
     * @throws Exception If random_bytes fails.
     */
    function generate_secure_key($length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('isImpersonating')) {
    function isImpersonating(): bool
    {
        return session()->has('impersonate');
    }
}

if (!function_exists('generate_slug')) {
    /**
     * Generate a unique slug from a string
     *
     * @param string $text
     * @param int $maxLength
     * @param string $prefix
     * @return string
     */
    function generate_slug(string $text, int $maxLength = 32, string $prefix = ''): string
    {
        // Convert to lowercase and replace spaces/special chars with dashes
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));

        // Remove multiple consecutive dashes
        $slug = preg_replace('/-+/', '-', $slug);

        // Add prefix if provided
        if ($prefix) {
            $slug = $prefix . $slug;
        }

        // Truncate to max length minus space for random suffix
        $randomSuffixLength = 10;
        $maxSlugLength = $maxLength - $randomSuffixLength - 1; // -1 for dash

        if (strlen($slug) > $maxSlugLength) {
            $slug = substr($slug, 0, $maxSlugLength);
            $slug = rtrim($slug, '-'); // Remove trailing dash
        }

        // Add random suffix to ensure uniqueness
        $randomSuffix = \Illuminate\Support\Str::random($randomSuffixLength);
        $slug .= '-' . strtolower($randomSuffix);

        return $slug;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format a number as currency
     *
     * @param float|string $amount
     * @param string $currency
     * @param int $decimals
     * @return string
     */
    function format_currency($amount, string $currency = '$', int $decimals = 2): string
    {
        return $currency . number_format((float) $amount, $decimals);
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize a filename for safe storage
     *
     * @param string $filename
     * @return string
     */
    function sanitize_filename(string $filename): string
    {
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        // Remove multiple dots
        $filename = preg_replace('/\.+/', '.', $filename);

        // Ensure it doesn't start with a dot
        $filename = ltrim($filename, '.');

        return $filename ?: 'file';
    }
}

if (!function_exists('get_file_extension')) {
    /**
     * Get file extension from filename
     *
     * @param string $filename
     * @return string
     */
    function get_file_extension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}

if (!function_exists('human_filesize')) {
    /**
     * Convert bytes to human readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    function human_filesize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('is_valid_image')) {
    /**
     * Check if file is a valid image
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool
     */
    function is_valid_image(\Illuminate\Http\UploadedFile $file): bool
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

        return in_array($file->getMimeType(), $allowedTypes) &&
            in_array(get_file_extension($file->getClientOriginalName()), $allowedExtensions);
    }
}
