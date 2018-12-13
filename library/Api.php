<?php


class Api {

    protected $options = [];

    public function __construct($options = [])
    {
        $default_options = array(
            'api_host'    => 'https://bitebtc.com',
            'public_key'  => 'your public_key',
            'private_key'  => 'your private_key',
        );
        $this->options = array_merge($default_options, $options);
    }


    public function set_option($key, $value)
    {
        $this->options[$key] = $value;
    }


    public function get($path, $params = [])
    {
        return $this->request($path, $params);
    }


    public function post($path, $params = [])
    {
        return $this->request($path, $params, 'POST');
    }


    public function request($path, $params = [], $canonical_verb='GET')
    {
        $headers = [];

        $tonce = time() * 1000;
        $api_host = $this->options['api_host'];

        ksort($params);

        $is_post = ($canonical_verb == 'POST');
        if (!$is_post) {
            $canonical_verb = 'GET';
        } else {
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'x-api-key: ' . $this->options['public_key'];
            $headers[] = 'x-api-tonce: ' . $tonce;
            $headers[] = 'x-api-signature: ' . hash_hmac('sha512', $path . $tonce . json_encode($params, JSON_UNESCAPED_UNICODE), $this->options['private_key']);
        }

        $canonical_uri = strtolower($path);
        if (substr($api_host , -1) == '/') {
            $api_host  = substr($api_host , 0, -1);
        }

        $url = $api_host . $path;
        $query_str = http_build_query($params);
        if ($is_post) {
            $content = $this->contents($url, json_encode($params, JSON_UNESCAPED_UNICODE), $headers);
        } else {
            if (!empty($params)) {
                $url .= '?' . $query_str;
            }
            $content = $this->contents($url, null, $headers);
        }

        if (empty($content)) {
            throw new Exception('Content is empty');
        }

        $obj = json_decode($content, true);
        if (empty($obj)) {
            throw new Exception('JSON decode failed, content: ' . $content);
        }
        return $obj;
    }


    public function contents($url, $post_params = null, $headers = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
        curl_setopt($ch, CURLOPT_USERAGENT, 'API Client');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!empty($post_params)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($post_params)) {
                $post_params = http_build_query($post_params);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        }
        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        if ($curl_errno > 0) {
            throw new Exception("cURL Error ($curl_errno): $curl_error, url: {$url}");
        }
        return $data;
    }
}