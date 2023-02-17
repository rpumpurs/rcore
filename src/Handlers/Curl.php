<?php

namespace RCore\Handlers;

class Curl
{
    public static function call(string $url, array $header = [], $isPost = false, bool $isPut = false, bool $isDelete = false): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($isPut) {
            curl_setopt($ch, CURLOPT_PUT, 1);
        }
        if ($isDelete) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($ch);

        return [json_decode($body, true), self::get_headers_from_curl_response($header)];
    }

    private static function get_headers_from_curl_response($response): array
    {
        $headers = array();

        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

        foreach (explode("\r\n", $header_text) as $i => $line)
            if ($i === 0)
                $headers['http_code'] = $line;
            else {
                list ($key, $value) = explode(': ', $line);

                $headers[strtolower($key)] = $value;
            }

        return $headers;
    }
}