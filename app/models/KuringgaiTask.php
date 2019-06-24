<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 6/24/2019
 * Time: 9:25 AM
 */

namespace Aiden\Models;
use Aiden\Models\Das;
use Aiden\Models\DeletedDa;

class KuringgaiTask extends _BaseModel
{

    public function init($actions, $da, $method ='get')
    {

        echo $da->getId() . ' - ' . $da->getCouncilUrl() . '<br>';
        // check documents;
        for($x = 0; $x < count($actions); $x++){
            switch ($actions[$x]){
                case 'documents':
                    $this->extractDocuments('', $da);
                    break;
            }
        }
    }

    public function extractDocuments($html, $da){
        $addedDocuments = 0;
        $url = 'https://eservicesdadocs.kmc.nsw.gov.au/WebGrid/?s=KCDocuments&Container='.urlencode($da->getCouncilReference()).'*';
        echo 'DOC URL' . ': ' . $url . '<br>';
        $output = $this->scrapeTo($url, true);
        $html = str_get_html($output['output']);
        if($html){
            $info = explode( "\n", $output['info']['request_header'] );
            $aspCookie = '';
            for($ac = 0; $ac < count($info); $ac++){
                if(strpos($info[$ac], 'Cookie') !== false){
                    $aspCookie = trim(str_replace('Cookie: ', '', $info[$ac]));
                }
            }
            $pageCount = 1;
            // count number of pages
            $rgNumPart = $html->find('.rgNumPart', 0);
            $targets = [];
            if($rgNumPart){
                $a = $rgNumPart->find('a');
                if( count($a) > 0){
                    $pageCount = count($a);
                    for($x = 1; $x < count($a); $x++){
                        $href = $a[$x]->getAttribute('href');
                        $targets[] = $this->get_string_between($href, "(&#39;", "&#39;,");
                    }

                }
            }

            $formData = $this->getAspFormDataByString($output['output']);
            $elements = $html->find("tr[class=rgRow], tr[class=rgAltRow]");
            foreach ($elements as $row){
                $docTitle = $this->cleanString($row->find('td', 1)->plaintext);
                $docUrl = $this->cleanString($row->find('td', 3)->find('a',0)->getAttribute('href'));
                $documentDate = new \DateTime(date('Y-m-d'));
//            $this->logger->info($docTitle. ': ' . $docUrl);

                if ($this->saveDocument($da, $docTitle, $docUrl, $documentDate) === true) {
                    $addedDocuments++;
                }

            }

            if($pageCount > 1){
                for($i = 0; $i < count($targets); $i++){
//                $formData['WebGrid1_rsmWebGridControl_TSM'] = ';;System.Web.Extensions, Version=4.0.0.0, Culture=neutral, PublicKeyToken=31bf3856ad364e35:en-US:b7585254-495e-4311-9545-1f910247aca5:ea597d4b:b25378d2;Telerik.Web.UI, Version=2016.3.1027.40, Culture=neutral, PublicKeyToken=121fae78165ba3d4:en-US:a5034868-8cfd-4375-ba8c-d3e7543c32f7:16e4e7cd:33715776:58366029';

                    $formData["__EVENTTARGET"] = $targets[$i];
                    $formDataQry = http_build_query($formData);
                    $requestHeaders = [
                    ];

                    $output = $this->postCurl($url, $formDataQry, $requestHeaders);
                    $html = str_get_html($output['output']);
                    $elements = $html->find("tr[class=rgRow], tr[class=rgAltRow]");
                    foreach ($elements as $row){
                        $docTitle = $this->cleanString($row->find('td', 1)->plaintext);
                        $docUrl = $this->cleanString($row->find('td', 3)->find('a',0)->getAttribute('href'));
                        $documentDate = new \DateTime(date('Y-m-d'));
                        echo $docTitle. ': ' . $docUrl . '<br>';

                        if ($this->saveDocument($da, $docTitle, $docUrl, $documentDate) === true) {
                            $addedDocuments++;
                        }

                    }
                }
            }
        }


        return ($addedDocuments > 0);
    }

    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }


    public function postCurl($url, $formData, $requestHeaders)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0');

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);

        return [
            'output' => $output,
            'errno' => $errno,
            'errmsg' => $errmsg
        ];
    }
}