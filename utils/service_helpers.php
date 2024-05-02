<?php

/* toJson */
if (!function_exists('toJson')) {
    function toJson($response, $code)
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($response);
        die;
    }
}

if (!function_exists('watzap_send_group')) {
    function watzap_send_group($group_id, $message)
    {
        $dataSending               = array();
        $dataSending["api_key"]    = WATZAP_API_KEY;
        $dataSending["number_key"] = WATZAP_NUMBER_KEY;
        $dataSending["group_id"]   = $group_id . "@g.us";
        $dataSending["message"]    = $message;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://api.watzap.id/v1/send_message_group',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($dataSending),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}

if (!function_exists('watzap_send')) {
    function watzap_send($phone, $message)
    {
        $dataSending               = array();
        $dataSending["api_key"]    = WATZAP_API_KEY;
        $dataSending["number_key"] = WATZAP_NUMBER_KEY;
        $dataSending["phone_no"]   = $phone;
        $dataSending["message"]    = $message;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://api.watzap.id/v1/send_message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($dataSending),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}

if (!function_exists('callAPI')) {
    function callAPI($method, $url, $data)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
            )
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}

if (!function_exists('curlGet')) {
    function curlGet($url, $headers = "")
    {
        $ch = curl_init();
        $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url,
            CURLOPT_TIMEOUT        => 20,
        ];

        if ($headers) {
            $options[CURLOPT_HTTPHEADER] =  $headers;
        }


        curl_setopt_array($ch, $options);
        $data = json_decode(curl_exec($ch));
        curl_close($ch);
        return $data;
        // var_dump($data);
    }
}

if (!function_exists('curlGet2')) {
    function curlGet2($url, $headers = "")
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ];

        if($headers) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}


if (!function_exists('curlPostJson')) {
    function curlPostJson($url, $data = array(), $headers = [])
    {
        // Create a new cURL resource
        $ch = curl_init($url);

        // Setup request to send json via POST
        $payload = json_encode($data);

        // Attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set the content type to application/json
        if (!$headers) {
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $rest = json_decode(curl_exec($ch));
        curl_close($ch);
        return $rest;
    }
}

if (!function_exists('build_post_fields')) {
    function build_post_fields($data, $existingKeys = '', &$returnArray = [])
    {
        if (($data instanceof CURLFile) or !(is_array($data) or is_object($data))) {
            $returnArray[$existingKeys] = $data;
            return $returnArray;
        } else {
            foreach ($data as $key => $item) {
                build_post_fields($item, $existingKeys ? $existingKeys . "[$key]" : $key, $returnArray);
            }
            return $returnArray;
        }
    }
}

if (!function_exists('curlPost')) {
    function curlPost($url, $data = array(), $headers = [])
    {
        $cURLConnection = curl_init($url);
        // dd($data);
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, build_post_fields($data));
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        // Set the content type to application/json
        if ($headers) {
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
        }

        $apiResponse = curl_exec($cURLConnection);
        $jsonArrayResponse = json_decode($apiResponse);
        curl_close($cURLConnection);
        // $apiResponse - available data from the API request
        return $jsonArrayResponse;
    }
}

if (!function_exists('requestJson')) {
    function requestJson($url = '', $method = '', $data = array(), $headers = [])
    {
        $curl = curl_init();
        $payload = json_encode($data);
        switch ($method) {
            case 'POST':
                if (!$headers) {
                    $headers[] = 'Content-Type: application/json';
                }
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_HTTPHEADER => $headers,
                ));

                $response = curl_exec($curl);
                $restData = json_decode($response);
                curl_close($curl);
                break;

            default:
                $restData = new stdClass();
                break;
        }
        return $restData;
    }
}

if (!function_exists('onesignal')) {
    function onesignal($role, $judul, $pesan)
    {

        $content = array(
            "en" => "$pesan"
        );
        $headings = array(
            "en" => "$judul"
        );

        $fields = array(
            'app_id' => "1f51a7a8-259d-4670-9e20-d33208aeaa4a",
            'filters' => array(array("field" => "tag", "key" => "role", "relation" => "=", "value" => "$role")),
            'included_segments' => array(
                'All'
            ),
            'data' => array("foo" => "bar"),
            'contents' => $content,
            'headings' => $headings,
            'url' => "https://appindocoll.com/"
        );

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic NmI2NzY0NzItOGQ0Mi00ZjEyLTlhZjgtZDJhMzY3NGRhZjlh'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        // var_dump($response);die;
        curl_close($ch);
    }
}
