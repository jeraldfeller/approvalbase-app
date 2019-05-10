<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 1/15/2019
 * Time: 10:18 AM
 */

namespace Aiden\Controllers;

use Aiden\Models\Das;
use Aiden\Models\DasDocuments;


use Aws\S3\S3Client;
use Aws\Credentials;
use Mailgun\Exception;

class PdfController extends _BaseController
{

    public function getDocumentUrlAction($limit = 100)
    {

        // fetch documents that is new or haven't process yet.
        $header = array(
            "Postman-Token: c66fe5de-765d-4714-8e1d-931528110dcd",
            "cache-control: no-cache"
        );
        $das = new Das();
        $sql = 'SELECT dd.id as docId, dd.name, dd.url, d.id, c.name as councilName
                FROM das d, das_documents dd, councils c
                WHERE dd.das_id = d.id
          
                AND d.council_id = c.id
                AND dd.as3_processed = 0
                AND (dd.status = 0 OR dd.status IS NULL)
                ORDER BY RAND()
                LIMIT ' . $limit;


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );
        $path = '';
        foreach ($result as $row) {
            $path = '';
            $docName = $row->name;
            $docId = $row->docId;
            $bol = $this->updateDocStatus($docId, true);
            if ($bol) {
                $url = $row->url;
                $dasId = $row->id;
                $council = $row->councilName;

                echo $council . ': ' . $url . '<br>';
                // fetch and download documents

                // Accept terms for some of councils
                switch ($council) {
                    case 'Penrith':
                        $termsUrl = 'http://bizsearch.penrithcity.nsw.gov.au/eplanning/Common/Common/Terms.aspx';
                        $acceptedTerms = $this->acceptTerms($termsUrl, $this->getAspFormDataByUrl($termsUrl), $council);
                        $info = explode( "\n", $acceptedTerms['info']['request_header'] );
                        $aspCookie = '';
                        for($ac = 0; $ac < count($info); $ac++){
                            if(strpos($info[$ac], 'Cookie') !== false){
                                $aspCookie = trim(str_replace('Cookie: ', '', $info[$ac]));
                            }
                        }
                        $header = [
                          "Cookie: $aspCookie"
                        ];
                        $pdfUrl = $this->curlCheckUrl($url, $header);

                        $baseName = str_replace([' ', '/'], '_',$docName);
                        $file = fopen('pdf/'.$docId.'_=_'.$baseName.'.pdf', "w");
                        fwrite($file, $pdfUrl['html']);
                        fclose($file);
                        break;
                    case 'Willoughby':
                        $termsUrl = 'https://eplanning.willoughby.nsw.gov.au/Common/Common/terms.aspx';
                        $formData = [
                          "ctl00_rcss_TSSM" => "",
                            "ctl00_script_TSM" => "",
                            "__EVENTTARGET" => "",
                            "__EVENTARGUMENT" => "",
                            "__VIEWSTATE" => "\/wEPDwUKMTQ2MzgzODU5OQ9kFgJmD2QWAgIDD2QWBAIEDxYCHgRUZXh0ZWQCCA9kFgICAQ9kFgQCAQ8WAh8ABfEaPGgxIHN0eWxlPSJ0ZXh0LWFsaWduOiBjZW50ZXI7Ij5ESVNDTEFJTUVSIDwvaDE+CjxwPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDEzcHg7Ij48c3Ryb25nPklmIHlvdSBhY2Nlc3MgdGhpcyB3ZWJzaXRlIHlvdSB3aWxsIGJlIHRha2VuIHRvIGhhdmUgYWdyZWVkIHRvIHRoZSBmb2xsb3dpbmcgVGVybXMgYW5kIENvbmRpdGlvbnM6Jm5ic3A7PC9zdHJvbmc+PC9zcGFuPjwvcD4KPGJyIC8+ClRoZSBjb250ZW50cyBvZiB0aGlzIHdlYnNpdGUgKHdoaWNoIGluY2x1ZGVzIGRvd25sb2FkYWJsZSBtYXRlcmlhbCkgYXJlIHN1YmplY3QgdG8gY29weXJpZ2h0IGFuZCBhcmUgcHJvdGVjdGVkIGJ5IGxhd3Mgb2YgQXVzdHJhbGlhIGFuZCBvdGhlciBjb3VudHJpZXMgdGhyb3VnaCBpbnRlcm5hdGlvbmFsIHRyZWF0aWVzLiA8YnIgLz4KPGJyIC8+CkNvdW5jaWwgZ3JhbnRzIHlvdSBhIG5vbi1leGNsdXNpdmUgbGljZW5jZSB0byByZXByb2R1Y2UgdGhlIGNvbnRlbnRzIG9mIHRoaXMgd2Vic2l0ZSBpbiB5b3VyIHdlYiBicm93c2VyIChhbmQgaW4gYW55IGNhY2hlIGZpbGUgcHJvZHVjZWQgYnkgeW91ciB3ZWIgYnJvd3NlcikgZm9yIHRoZSBzb2xlIHB1cnBvc2Ugb2Ygdmlld2luZyB0aGUgY29udGVudC4gQ291bmNpbCByZXNlcnZlcyBhbGwgb3RoZXIgcmlnaHRzLiA8YnIgLz4KPGJyIC8+ClRoZSBpbmZvcm1hdGlvbiBwcm92aWRlZCBvbiB0aGlzIHdlYnNpdGUgaXMgdG8gYXNzaXN0IGN1c3RvbWVycyBpbiB0cmFja2luZyB0aGUgcHJvZ3Jlc3Mgb2YgQXBwbGljYXRpb25zLiBJdCByZXByZXNlbnRzIGtleSBtaWxlc3RvbmVzIGluIHRoZSBBcHBsaWNhdGlvbiBwcm9jZXNzIGJ1dCBpcyBub3QgYSBkZXRhaWxlZCBoaXN0b3J5LiBQZXJzb25zIHdpc2hpbmcgdG8gY29uZmlybSBpbmZvcm1hdGlvbiBpbiBkZXRhaWwgc2hvdWxkIGNvbnRhY3QgQ291bmNpbCB2aWEgZWl0aGVyIHRoZSBlbWFpbCBmYWNpbGl0eSBvciBpbiB3cml0aW5nIGluIG9yZGVyIHRvIG9idGFpbiBhIHdyaXR0ZW4gcmVzcG9uc2UuIDxiciAvPgo8YnIgLz4KVGhlIGluZm9ybWF0aW9uIHByb3ZpZGVkIGJ5IHRoZSBTZXJ2aWNlIG1heSBjb250YWluIGluYWNjdXJhY2llcyBvciBlcnJvcnMuIENvdW5jaWwgbWFrZXMgbm8gd2FycmFudGllcyBvciByZXByZXNlbnRhdGlvbnMgcmVnYXJkaW5nIHRoZSBjdXJyZW5jeSwgcXVhbGl0eSwgYWNjdXJhY3ksIG1lcmNoYW50YWJpbGl0eSBvciBmaXRuZXNzIGZvciB0aGUgcHVycG9zZSBvZiB0aGUgaW5mb3JtYXRpb24gcHJvdmlkZWQgYnkgdGhlIFNlcnZpY2UsIG9yIHRoYXQgdGhlIFNlcnZpY2UgaXMgZnJlZSBmcm9tIGFueSB2aXJ1cyBvciBvdGhlciBkZWZlY3QuIEl0IGlzIHlvdXIgc29sZSByZXNwb25zaWJpbGl0eSB0byBtYWtlIHlvdXIgb3duIGFzc2Vzc21lbnQgb2YgdGhlIGluZm9ybWF0aW9uIHByb3ZpZGVkIGJ5IHRoZSBTZXJ2aWNlLjxiciAvPgo8YnIgLz4KQ291bmNpbCB3aWxsIG5vdCBpbiBhbnkgY2lyY3Vtc3RhbmNlcyBiZSBsaWFibGUgdG8geW91IGZvciBhbnkgbG9zcyBvciBkYW1hZ2UgKGluY2x1ZGluZyB3aXRob3V0IGxpbWl0YXRpb24sIGNvbnNlcXVlbnRpYWwgbG9zcyBvciBkYW1hZ2UpIGhvd2V2ZXIgY2F1c2VkIGFuZCB3aGV0aGVyIGFyaXNpbmcgZGlyZWN0bHkgb3IgaW5kaXJlY3RseSBmcm9tIHlvdXIgdXNlIG9mIHRoZSBpbmZvcm1hdGlvbiBwcm92aWRlZCBieSB0aGUgU2VydmljZS4gPGJyIC8+CjxiciAvPgpZb3UgYWNrbm93bGVkZ2UgdGhhdCBiZWNhdXNlIG9mIHRoZSBuYXR1cmUgb2YgdGhlIGludGVybmV0IGFuZCB0aGlyZCBwYXJ0eSBkZXBlbmRlbmNpZXMsIENvdW5jaWwgZG9lcyBub3Qgd2FycmFudCB0aGF0IGFjY2VzcyB0byBvciB1c2Ugb2YgdGhlIFNlcnZpY2Ugd2lsbCBiZSBjb250aW51b3VzIG9yIHVuaW50ZXJydXB0ZWQuIEZyb20gdGltZSB0byB0aW1lIHRoZSBTZXJ2aWNlIG1heSBub3QgYmUgYXZhaWxhYmxlIGR1ZSB0byB1cGdyYWRlcyBvciBtYWludGVuYW5jZS4pPGJyIC8+CkNvdW5jaWwgcmVzZXJ2ZXMgdGhlIHJpZ2h0IHRvIGNoYW5nZSBhbnkgYXNwZWN0IG9mIHRoZSBTZXJ2aWNlLCBpbmNsdWRpbmcgbW9kaWZ5aW5nLCBzdXNwZW5kaW5nLCByZXBsYWNpbmcgb3IgdGVybWluYXRpbmcgdGhlIFNlcnZpY2UgYXMgd2VsbCBhcyBpbXBvc2luZyBhbnkgZmVlIGZvciBhY2Nlc3MgdG8gdGhlIFNlcnZpY2UuIENvdW5jaWwgd2lsbCBoYXZlIG5vIGxpYWJpbGl0eSB0byB5b3UgaWYgdGhlIFNlcnZpY2UgaXMgbW9kaWZpZWQsIHN1c3BlbmRlZCwgcmVwbGFjZWQgb3IgdGVybWluYXRlZC4gWW91IGFncmVlIHRvIHdhaXZlIGFsbCByaWdodHMgeW91IG1heSBoYXZlIGFnYWluc3QgQ291bmNpbCBpbiByZXNwZWN0IG9mIGFueSBzdWNoIGFjdGlvbiB0YWtlbiBieSBDb3VuY2lsLjxiciAvPgo8YnIgLz4KWW91IGFncmVlIG5vdCB0byBpbnRlcmZlcmUgd2l0aCB0aGUgcHJvcGVyIHdvcmtpbmcgb2YgdGhlIFNlcnZpY2UuIFlvdSBhZ3JlZSBub3QgdG8gZG8gYW55dGhpbmcgdGhhdCBpbXBvc2VzIGFuIHVucmVhc29uYWJsZSBvciBkaXNwcm9wb3J0aW9uYXRlbHkgbGFyZ2UgbG9hZCBvbiB0aGUgU2VydmljZSwgb3IgdXNlIHRoZSBTZXJ2aWNlIG90aGVyIHRoYW4gdG8gZ2FpbiBpbmZvcm1hdGlvbi4gPGJyIC8+CjxiciAvPgo8c3Bhbj5BbGwgbWF0dGVycyByZWxhdGluZyB0byB0aGlzIHdlYnNpdGUgYXJlIGdvdmVybmVkIGJ5IHRoZSBsYXdzIG9mIHRoZSBTdGF0ZSBvZiBOU1csIEF1c3RyYWxpYS4gPC9zcGFuPkJ5IGFjY2Vzc2luZyB0aGlzIGluZm9ybWF0aW9uIEkgcmVxdWVzdCB0byBkbyBzbyB1bmRlciB0aGUgR292ZXJubWVudCBJbmZvcm1hdGlvbiAoUHVibGljIEFjY2VzcykgQWN0IDIwMDkgKEdJUEEgQWN0KSBhbmQgSSB1bmRlcnN0YW5kIHRoYXQgQ291bmNpbCBpcyBtYWtpbmcgdGhlIGluZm9ybWF0aW9uIGF2YWlsYWJsZSB1bmRlciB0aGUgcHJvdmlzaW9ucyBvZiB0aGUgR0lQQSBBY3QuIDxiciAvPgo8YnIgLz4KUHJpdmFjeSBOb3RpZmljYXRpb24gLSBJbmZvcm1hdGlvbiBwcm92aWRlZCB0byBDb3VuY2lsIGluIGNvcnJlc3BvbmRlbmNlLCBzdWJtaXNzaW9ucyBvciByZXF1ZXN0cyAodmVyYmFsLCBlbGVjdHJvbmljIG9yIHdyaXR0ZW4pLCBpbmNsdWRpbmcgcGVyc29uYWwgaW5mb3JtYXRpb24gc3VjaCBhcyB5b3VyIG5hbWUgYW5kIGFkZHJlc3MsIG1heSBiZSBtYWRlIHB1YmxpY2x5IGF2YWlsYWJsZSwgaW5jbHVkaW5nIHZpYSBDb3VuY2lsIHdlYnNpdGUsIGluIGFjY29yZGFuY2Ugd2l0aCB0aGUgR292ZXJubWVudCBJbmZvcm1hdGlvbiAoUHVibGljIEFjY2VzcykgQWN0IChHSVBBIEFjdCkgMjAwOS4gQ291bmNpbCByZXNlcnZlcyB0aGUgcmlnaHQgdG8gcmVwcm9kdWNlIGluIHdob2xlIG9yIGluIHBhcnQgYW55IGNvcnJlc3BvbmRlbmNlIG9yIHN1Ym1pc3Npb24uJm5ic3A7ZAIJDxYCHwBlZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAQUaY3RsMDAkY3RNYWluJGNoa0FncmVlJGNoazGNmhzo+YGNHNog3bdr8Jbf6DdgPQ==",
                            "__VIEWSTATEGENERATOR" => "A8DC4E82",
                            "__EVENTVALIDATION" => "\/wEdAATJB0NxPvcVWf\/FLQLibO1VOcBhpQsJgyMjpoz897IvWkTdyBz\/e\/CcgfJ5zxj4jwg\/VxUvaVtQChmSC3DOB0MpRrO5C68mFmt0JepQoG0l9w66+GI="
                            ];



                        $this->acceptTerms($termsUrl, $formData, $council);
                        $pdfUrl = $this->curlCheckUrl($url, [], true);
                        $baseName = str_replace([' ', '/'], '_',$docName);


                        // Delete pdf if not exists
                        if(strpos($pdfUrl['html'], 'Page does not exist.') === false){
                            $file = fopen('pdf/'.$docId.'_=_'.$baseName.'.pdf', "w");
                            fwrite($file, $pdfUrl['html']);
                            fclose($file);
                        }else{
                            // Delete pdf
                            $this->deletePdfById($docId);
                            echo $pdfUrl['html'] . '<Br>';
                        }
                        break;
                    default:
                        $pdfUrl = $this->curlCheckUrl($url, $header);
                        if(isset($pdfUrl['error'])){
                            $this->setErrorMessage($docId, $pdfUrl['error']);

                        }
                        break;
                }





                if ($pdfUrl['url'] != false) {
//                    $file = fopen('pdf/test'.$docId.'.html', "w");
//                    fwrite($file, $pdfUrl['html']);
//                    fclose($file);

                    // Delete pdf if not exists
                    if(strpos($pdfUrl['html'], 'Requested file does not exist.') === false){
                        $pdfUrl['url'] = trim(str_replace(' ', '%20', $pdfUrl['url']));
                        switch ($council) {
                            case 'Bankstown':
                                $parseUrl = parse_url($pdfUrl['url']);
                                parse_str($parseUrl['query']);
                                $qryTitle = $title;
                                $qryTitle = $docName;
                                $path = 'pdf/' . $docId . '_=_' . str_replace([' ', '/'], '_', $qryTitle) . '.pdf';
                                $pdfUrl = trim($pdfUrl['url']);
                                break;
                            case 'Camden':
                                parse_str(basename($pdfUrl['url']));
                                $qryTitle = $fileName;
                                $qryTitle = $docName;
                                $path = 'pdf/' . $docId . '_=_' . str_replace([' ', '/'], '_', $qryTitle) . '.pdf';
                                $pdfUrl = trim($pdfUrl['url']);
                                break;
                            case 'Fairfield City':
                                $html = str_get_html(str_replace('%20', ' ', $pdfUrl['html']));
                                $iframe = $html->find('iframe', 0);
                                if ($iframe) {
                                    $src = str_replace('../../', '', $iframe->getAttribute('src'));
                                    $srcPath = explode('/', $src);
                                    $srcPath = str_replace([' ', '/'], '_', $docName);
                                    $pdfUrl = 'https://openaccess.fairfieldcity.nsw.gov.au/OpenAccess/' . $srcPath[0] . '/' . $srcPath[1];
                                    $path = 'pdf/' . $docId . '_=_' . $srcPath[1];
                                } else {
                                    $path = '';
                                }
                                break;
                            case 'Georges River':
                                $html = str_get_html(str_replace('%20', ' ', $pdfUrl['html']));
                                $iframe = $html->find('iframe', 0);
                                if ($iframe) {

                                    $src = str_replace('../../', '', $iframe->getAttribute('src'));
                                    $pdfUrl = $src;
//                                $path = 'pdf/' . $docId . '_=_' . basename($pdfUrl);
                                    $path = 'pdf/' . $docId . '_=_' . str_replace([' ', '/'], '_', $docName);
                                } else {
                                    $path = '';
                                }
                                break;
                            default:
                                $pdfData = $pdfUrl['html'];
                                $pdfUrl = trim($pdfUrl['url']);
                                $baseName = basename($pdfUrl);
                                $baseName = str_replace([' ', '/'], '_',$docName);
                                $path = 'pdf/' . $docId . '_=_' . $this->clean($baseName);
                                break;

                        }
                    }else{
                        // Delete pdf
                        $this->deletePdfById($docId);
                        echo $pdfUrl['html'] . '<Br>';
                    }

                    if ($path != '') {
                        if($council != 'Penrith' && $council != 'Willoughby') {
                            if (!strpos($path, '.doc') && !strpos($path, '.DOC')) {
                                $path = (!strpos($path, '.pdf') && !strpos($path, '.PDF') ? $path . '.pdf' : $path);
                            }
                            $fopen = @fopen($pdfUrl, 'r');
                            if($fopen !== false){
                                file_put_contents($path, $fopen);
                            }else{
                                // Delete Docs in database
                                $this->deletePdfById($docId);
                            }
                        }
                    }
                }
            }

        }

//        echo 'PATH: ' . $path . '<br>';
//        if ($path != '') {
//            if($council != 'Penrith' && $council != 'Willoughby'){
//                var_dump('2nd');
//                if (!strpos($path, '.doc') && !strpos($path, '.DOC')) {
//                    $path = (!strpos($path, '.pdf') && !strpos($path, '.PDF') ? $path . '.pdf' : $path);
//                }
//                file_put_contents($path, fopen($pdfUrl, 'r'));
//            }
//
//        }
        return true;
    }


    public function deletePdfById($id){
        $dd = DasDocuments::findFirst([
            'conditions' => 'id = ' . $id
        ]);
        if($dd){
            $dd->delete();
        }

        return true;
    }

    public function setErrorMessage($id, $message){
        $dd = DasDocuments::findFirst([
            'conditions' => 'id = ' . $id
        ]);
        if($dd){
            $dd->setErrorMessage($message);
            $dd->save();
        }

        return true;
    }

    public function uploadToAmazonS3()
    {
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();
        $credentials = new \Aws\Credentials\Credentials($config->as3->key, $config->as3->secret);
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => $credentials,
            'debug' => false
        ]);
        $dir = $_SERVER["DOCUMENT_ROOT"] . '/public/pdf';
//        $dir = $_SERVER["DOCUMENT_ROOT"] . '/pdf';
        $files = array_diff(scandir($dir, 1), ['.', '..']);
        if (count($files) > 0) {
            $source = $dir;
            // Where the files will be transferred to
//            $dest = 's3://ab-pdf-storage';
            $dest = 's3://approvalbase-pdf-storage';

            // Create a transfer object
            $manager = new \Aws\S3\Transfer($s3, $source, $dest);

            // Perform the transfer synchronously
            $manager->transfer();
            for ($x = 0; $x < count($files); $x++) {
                $filePath = $dir . '/' . $files[$x];
                echo $filePath . '<br>';
                $docId = explode('_=_', $files[$x])[0];
                unlink($filePath);
                $this->updateDocStatus($docId, true, true, $config->as3->endPoint.$files[$x]);

            }

        }
    }
    public function updateDocStatus($id, $as = false, $status = false, $as3Link = '')
    {
        $doc = DasDocuments::findFirst(
            [
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $id
                ]
            ]
        );
        if ($doc) {
            $doc->setAs3Processed($as);
            $doc->setStatus($status);
            if($as3Link != ''){
                $doc->setAs3Url($as3Link);
            }
            $doc->setDate(new \DateTime());
            $doc->save();
            return true;
        } else {
            return false;
        }
    }

    public function downloadAction()
    {
        $file = urldecode($this->request->getQuery("file"));
        // Process download
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            flush(); // Flush system output buffer
            readfile($file);
            exit;
        } else {
            echo 'error';
        }

    }

    public function downloadPdfAction()
    {
        $id = $this->request->getPost('id');
        $part = $this->request->getPost('part');
//        $council = $this->request->getPost('council');


        if($part == 1){
            $docs = DasDocuments::find([
                'conditions' => 'das_id = :id:',
                'bind' => [
                    'id' => $id
                ]
            ]);

            $filesToZip = array();
            foreach ($docs as $row) {
                $url = $row->getAs3Url();
                if ($url != false) {
                    $baseName = basename($url);
                    $baseNameArray = explode('_=_', $baseName);
                    $pdfFileName = $row->getName().'.pdf';
                    $path = 'pdf-zip/' . str_replace(['/','\\'], '_', $pdfFileName);
                    if ($path != '') {
                        $filesToZip[] = [
                            'path' => $path,
                            'url' => $url
                            ];
                    }

                }
            }
            echo json_encode($filesToZip);
        }elseif($part == 2){
            $index = $this->request->getPost('index');
            $filesToZip = $this->request->getPost('file');
            file_put_contents($filesToZip['path'], fopen($filesToZip['url'], 'r'));
            echo json_encode([
                'nextIndex' => $index + 1
            ]);
        }else{
            $filesToZip = $this->request->getPost('file');
            // check if there is PDF
            if (count($filesToZip) > 0) {
                $filesToZip = array_unique($filesToZip);
                $filesToZip = array_values($filesToZip);

                $zipname = 'pdf-zip/' . $this->getUser()->getId() . '_' . time() . '.zip';
                $zip = new \ZipArchive;
                $zip->open($zipname, \ZipArchive::CREATE);
                for ($i = 0; $i < count($filesToZip); $i++) {
                    $zip->addFile($filesToZip[$i]);
                }
                $zip->close();

                for ($i = 0; $i < count($filesToZip); $i++) {
                    unlink($filesToZip[$i]);
                }
                echo json_encode(['s' => 1, 'file' => $zipname]);
            } else {
                echo json_encode(false);
            }
        }




    }

    
    



    public function curlCheckUrl($url, $header = [], $useCookie = false)
    {
        $url = trim($url);
        $curl = curl_init();
        if($useCookie == true){
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_FOLLOWLOCATION => 1, // follow redirects
                CURLOPT_AUTOREFERER => 1, // set referer on redirect
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_COOKIEFILE => $this->config->directories->cookiesDir . 'cookies.txt',
                CURLOPT_COOKIEJAR => $this->config->directories->cookiesDir . 'cookies.txt'
            ));
        }else{
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_FOLLOWLOCATION => 1, // follow redirects
                CURLOPT_AUTOREFERER => 1, // set referer on redirect
                CURLOPT_HTTPHEADER => $header
            ));
        }


        $response = curl_exec($curl);
        $target = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        $err = curl_error($curl);


        curl_close($curl);

        if ($err) {
            return ['error' => $err, 'url' => false];
        } else {
            if ($target == false) {
                return ['url' => $url, 'html' => $response];
            } else {

                return ['url' => $target, 'html' => $response];
            }
        }
    }


    public function getTitle($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_FOLLOWLOCATION => 1, // follow redirects
            CURLOPT_AUTOREFERER => 1, // set referer on redirect
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: c66fe5de-765d-4714-8e1d-931528110dcd",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return false;
        } else {
            return $response;
        }

    }

    public function curlPdfDownload($url)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: c66fe5de-765d-4714-8e1d-931528110dcd",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return false;
        } else {
            return $url;
        }
    }


    public function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }


    public function getAspFormDataByUrl($url) {

        $requestHeaders = [
            'Accept: */*; q=0.01',
            'Accept-Encoding: none'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->config->dev);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$this->config->dev);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config->directories->cookiesDir . 'cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config->directories->cookiesDir . 'cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, $this->config->useragent);

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);

        curl_close($ch);

        // No errors
        if ($errno !== 0) {
            // TODO: Log
            return false;
        }

        $formData = $this->getAspFormDataByString($output);

        return $formData;

    }


    public function getAspFormDataByString($string) {

        // Extract __VIEWSTATE, __VIEWSTATEGENERATOR, and other asp puke
        $html = str_get_html($string);
        if (!$html) {
            // TODO: Log that HTML couldn't be parsed.
            return false;
        }

        $formData = [];

        $elements = $html->find("input[type=hidden]");
        foreach ($elements as $element) {

            if (isset($element->id) && isset($element->value)) {
                $formData[$element->id] = html_entity_decode($element->value, ENT_QUOTES);
            }
        }

        return $formData;

    }

    public function acceptTerms($url, $formData, $council, $requestHeaders = []) {

        switch ($council){
            case 'Penrith':
                $formData['ctl00$ctMain1$chkAgree$chk1'] = 'on';
                $formData['ctl00$ctMain1$BtnAgree'] = 'I Agree';
                $formData = http_build_query($formData);
//                $requestHeaders = [
//                    "Host: bizsearch.penrithcity.nsw.gov.au",
//                    "Origin: http://bizsearch.penrithcity.nsw.gov.au",
//                    "Referer: http://bizsearch.penrithcity.nsw.gov.au/eplanning/Common/Common/Terms.aspx",
//                    "Content-Length: " . strlen($formData)
//                ];
                break;
            case 'Willoughby':
                $formData["ctl00_rcss_TSSM"] = null;
                $formData['ctl00$ctMain$BtnAgree'] = "I Agree";
                $formData['ctl00$ctMain$chkAgree$chk1'] = "on";
                $formData = http_build_query($formData);

                $requestHeaders = [
                    "Accept: */*; q=0.01",
                    "Accept-Encoding: none",
                    "Content-Type: application/x-www-form-urlencoded",
                    "Content-Length: " . strlen($formData)
                ];
                break;
        }

//        var_dump($url);
//        var_dump($formData);
//

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->config->dev);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$this->config->dev);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config->directories->cookiesDir . 'cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config->directories->cookiesDir . 'cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, $this->config->useragent);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $output = curl_exec($ch);
        $information = curl_getinfo($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);

   
//        $file = fopen("test-2.html", "w");
//        echo fwrite($file, $output);
//        fclose($file);

        curl_close($ch);

        if ($errno !== 0) {

            $message = "cURL error: " . $errmsg . " (" . $errno . ")";
            $this->logger->error($message);
            return false;
        }

        return [
            'info' => $information
        ];

    }


    public function postCurl($url, $formData, $requestHeaders)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->config->dev);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$this->config->dev);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config->directories->cookiesDir . 'cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config->directories->cookiesDir . 'cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, $this->config->useragent);


        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        echo $output;
        return [
            'output' => $output,
            'errno' => $errno,
            'errmsg' => $errmsg
        ];
    }

}