<?php

namespace Aiden\Classes;

class SwissKnife {

    public static function getOutput($url, $post = false, $postParams = []) {

        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();

        $headers = [
            'Accept:*/*',
            'Accept-Encoding:none',
            'Accept-Language:en-GB,en-US;q=0.9,en;q=0.8',
            'Cache-Control:no-cache',
            'Connection:keep-alive',
            'DNT:1',
            'Pragma:no-cache',
            'Upgrade-Insecure-Requests:1',
        ];

        // Get output from Base URL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $config->application->curlUserAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, $config->application->curlTimeout);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $config->application->directories->cookiesDir . 'cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $config->application->directories->cookiesDir . 'cookies.txt');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$config->application->dev);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$config->application->dev);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch, CURLOPT_PROXY, '108.62.54.111');
        curl_setopt($ch, CURLOPT_PROXYPORT, '47647');
        curl_setopt($ch, PROXYUSERPWD, 'ebymarket:dfab7c358');
        if ($post === true) {

            curl_setopt($ch, CURLOPT_POST, 1);

            if (count($postParams) > 0) {

                curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
            }
        }

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);



        if ($errno === 0) {
            return $output;
        }
        else {
            return false;
        }

    }

    public static function br2nl($string) {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);

    }

    public static function rel2abs($rel, $base) {
        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '') {
            return $rel;
        }

        /* queries and anchors */
        if ($rel[0] == '#' || $rel[0] == '?') {
            return $base . $rel;
        }

        /* parse base URL and convert to local variables:
          $scheme, $host, $path */
        extract(parse_url($base));

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/') {
            $path = '';
        }

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {

        }

        /* absolute URL is ready! */
        return $scheme . '://' . $abs;

    }

    public static function getDomainFromUrl($url) {

        $urlobj = parse_url($url);
        $domain = $urlobj['host'];

        if (!isset($urlobj['host'])) {
            echo $url;
            exit();
        }

        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $matches)) {
            return $matches['domain'];
        }
        return false;

    }

}
