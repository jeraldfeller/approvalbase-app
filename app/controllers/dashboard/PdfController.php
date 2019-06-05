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
                        $info = explode("\n", $acceptedTerms['info']['request_header']);
                        $aspCookie = '';
                        for ($ac = 0; $ac < count($info); $ac++) {
                            if (strpos($info[$ac], 'Cookie') !== false) {
                                $aspCookie = trim(str_replace('Cookie: ', '', $info[$ac]));
                            }
                        }
                        $header = [
                            "Cookie: $aspCookie"
                        ];
                        $pdfUrl = $this->curlCheckUrl($url, $header);

                        $baseName = str_replace([' ', '/'], '_', $docName);
                        $file = fopen('pdf/' . $docId . '_=_' . $baseName . '.pdf', "w");
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
                            "__VIEWSTATE" => "/wEPDwUKMTQ2MzgzODU5OQ9kFgJmD2QWAgIDD2QWBAIEDxYCHgRUZXh0ZWQCCA9kFgICAQ9kFgQCAQ8WAh8ABfEaPGgxIHN0eWxlPSJ0ZXh0LWFsaWduOiBjZW50ZXI7Ij5ESVNDTEFJTUVSIDwvaDE+CjxwPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDEzcHg7Ij48c3Ryb25nPklmIHlvdSBhY2Nlc3MgdGhpcyB3ZWJzaXRlIHlvdSB3aWxsIGJlIHRha2VuIHRvIGhhdmUgYWdyZWVkIHRvIHRoZSBmb2xsb3dpbmcgVGVybXMgYW5kIENvbmRpdGlvbnM6Jm5ic3A7PC9zdHJvbmc+PC9zcGFuPjwvcD4KPGJyIC8+ClRoZSBjb250ZW50cyBvZiB0aGlzIHdlYnNpdGUgKHdoaWNoIGluY2x1ZGVzIGRvd25sb2FkYWJsZSBtYXRlcmlhbCkgYXJlIHN1YmplY3QgdG8gY29weXJpZ2h0IGFuZCBhcmUgcHJvdGVjdGVkIGJ5IGxhd3Mgb2YgQXVzdHJhbGlhIGFuZCBvdGhlciBjb3VudHJpZXMgdGhyb3VnaCBpbnRlcm5hdGlvbmFsIHRyZWF0aWVzLiA8YnIgLz4KPGJyIC8+CkNvdW5jaWwgZ3JhbnRzIHlvdSBhIG5vbi1leGNsdXNpdmUgbGljZW5jZSB0byByZXByb2R1Y2UgdGhlIGNvbnRlbnRzIG9mIHRoaXMgd2Vic2l0ZSBpbiB5b3VyIHdlYiBicm93c2VyIChhbmQgaW4gYW55IGNhY2hlIGZpbGUgcHJvZHVjZWQgYnkgeW91ciB3ZWIgYnJvd3NlcikgZm9yIHRoZSBzb2xlIHB1cnBvc2Ugb2Ygdmlld2luZyB0aGUgY29udGVudC4gQ291bmNpbCByZXNlcnZlcyBhbGwgb3RoZXIgcmlnaHRzLiA8YnIgLz4KPGJyIC8+ClRoZSBpbmZvcm1hdGlvbiBwcm92aWRlZCBvbiB0aGlzIHdlYnNpdGUgaXMgdG8gYXNzaXN0IGN1c3RvbWVycyBpbiB0cmFja2luZyB0aGUgcHJvZ3Jlc3Mgb2YgQXBwbGljYXRpb25zLiBJdCByZXByZXNlbnRzIGtleSBtaWxlc3RvbmVzIGluIHRoZSBBcHBsaWNhdGlvbiBwcm9jZXNzIGJ1dCBpcyBub3QgYSBkZXRhaWxlZCBoaXN0b3J5LiBQZXJzb25zIHdpc2hpbmcgdG8gY29uZmlybSBpbmZvcm1hdGlvbiBpbiBkZXRhaWwgc2hvdWxkIGNvbnRhY3QgQ291bmNpbCB2aWEgZWl0aGVyIHRoZSBlbWFpbCBmYWNpbGl0eSBvciBpbiB3cml0aW5nIGluIG9yZGVyIHRvIG9idGFpbiBhIHdyaXR0ZW4gcmVzcG9uc2UuIDxiciAvPgo8YnIgLz4KVGhlIGluZm9ybWF0aW9uIHByb3ZpZGVkIGJ5IHRoZSBTZXJ2aWNlIG1heSBjb250YWluIGluYWNjdXJhY2llcyBvciBlcnJvcnMuIENvdW5jaWwgbWFrZXMgbm8gd2FycmFudGllcyBvciByZXByZXNlbnRhdGlvbnMgcmVnYXJkaW5nIHRoZSBjdXJyZW5jeSwgcXVhbGl0eSwgYWNjdXJhY3ksIG1lcmNoYW50YWJpbGl0eSBvciBmaXRuZXNzIGZvciB0aGUgcHVycG9zZSBvZiB0aGUgaW5mb3JtYXRpb24gcHJvdmlkZWQgYnkgdGhlIFNlcnZpY2UsIG9yIHRoYXQgdGhlIFNlcnZpY2UgaXMgZnJlZSBmcm9tIGFueSB2aXJ1cyBvciBvdGhlciBkZWZlY3QuIEl0IGlzIHlvdXIgc29sZSByZXNwb25zaWJpbGl0eSB0byBtYWtlIHlvdXIgb3duIGFzc2Vzc21lbnQgb2YgdGhlIGluZm9ybWF0aW9uIHByb3ZpZGVkIGJ5IHRoZSBTZXJ2aWNlLjxiciAvPgo8YnIgLz4KQ291bmNpbCB3aWxsIG5vdCBpbiBhbnkgY2lyY3Vtc3RhbmNlcyBiZSBsaWFibGUgdG8geW91IGZvciBhbnkgbG9zcyBvciBkYW1hZ2UgKGluY2x1ZGluZyB3aXRob3V0IGxpbWl0YXRpb24sIGNvbnNlcXVlbnRpYWwgbG9zcyBvciBkYW1hZ2UpIGhvd2V2ZXIgY2F1c2VkIGFuZCB3aGV0aGVyIGFyaXNpbmcgZGlyZWN0bHkgb3IgaW5kaXJlY3RseSBmcm9tIHlvdXIgdXNlIG9mIHRoZSBpbmZvcm1hdGlvbiBwcm92aWRlZCBieSB0aGUgU2VydmljZS4gPGJyIC8+CjxiciAvPgpZb3UgYWNrbm93bGVkZ2UgdGhhdCBiZWNhdXNlIG9mIHRoZSBuYXR1cmUgb2YgdGhlIGludGVybmV0IGFuZCB0aGlyZCBwYXJ0eSBkZXBlbmRlbmNpZXMsIENvdW5jaWwgZG9lcyBub3Qgd2FycmFudCB0aGF0IGFjY2VzcyB0byBvciB1c2Ugb2YgdGhlIFNlcnZpY2Ugd2lsbCBiZSBjb250aW51b3VzIG9yIHVuaW50ZXJydXB0ZWQuIEZyb20gdGltZSB0byB0aW1lIHRoZSBTZXJ2aWNlIG1heSBub3QgYmUgYXZhaWxhYmxlIGR1ZSB0byB1cGdyYWRlcyBvciBtYWludGVuYW5jZS4pPGJyIC8+CkNvdW5jaWwgcmVzZXJ2ZXMgdGhlIHJpZ2h0IHRvIGNoYW5nZSBhbnkgYXNwZWN0IG9mIHRoZSBTZXJ2aWNlLCBpbmNsdWRpbmcgbW9kaWZ5aW5nLCBzdXNwZW5kaW5nLCByZXBsYWNpbmcgb3IgdGVybWluYXRpbmcgdGhlIFNlcnZpY2UgYXMgd2VsbCBhcyBpbXBvc2luZyBhbnkgZmVlIGZvciBhY2Nlc3MgdG8gdGhlIFNlcnZpY2UuIENvdW5jaWwgd2lsbCBoYXZlIG5vIGxpYWJpbGl0eSB0byB5b3UgaWYgdGhlIFNlcnZpY2UgaXMgbW9kaWZpZWQsIHN1c3BlbmRlZCwgcmVwbGFjZWQgb3IgdGVybWluYXRlZC4gWW91IGFncmVlIHRvIHdhaXZlIGFsbCByaWdodHMgeW91IG1heSBoYXZlIGFnYWluc3QgQ291bmNpbCBpbiByZXNwZWN0IG9mIGFueSBzdWNoIGFjdGlvbiB0YWtlbiBieSBDb3VuY2lsLjxiciAvPgo8YnIgLz4KWW91IGFncmVlIG5vdCB0byBpbnRlcmZlcmUgd2l0aCB0aGUgcHJvcGVyIHdvcmtpbmcgb2YgdGhlIFNlcnZpY2UuIFlvdSBhZ3JlZSBub3QgdG8gZG8gYW55dGhpbmcgdGhhdCBpbXBvc2VzIGFuIHVucmVhc29uYWJsZSBvciBkaXNwcm9wb3J0aW9uYXRlbHkgbGFyZ2UgbG9hZCBvbiB0aGUgU2VydmljZSwgb3IgdXNlIHRoZSBTZXJ2aWNlIG90aGVyIHRoYW4gdG8gZ2FpbiBpbmZvcm1hdGlvbi4gPGJyIC8+CjxiciAvPgo8c3Bhbj5BbGwgbWF0dGVycyByZWxhdGluZyB0byB0aGlzIHdlYnNpdGUgYXJlIGdvdmVybmVkIGJ5IHRoZSBsYXdzIG9mIHRoZSBTdGF0ZSBvZiBOU1csIEF1c3RyYWxpYS4gPC9zcGFuPkJ5IGFjY2Vzc2luZyB0aGlzIGluZm9ybWF0aW9uIEkgcmVxdWVzdCB0byBkbyBzbyB1bmRlciB0aGUgR292ZXJubWVudCBJbmZvcm1hdGlvbiAoUHVibGljIEFjY2VzcykgQWN0IDIwMDkgKEdJUEEgQWN0KSBhbmQgSSB1bmRlcnN0YW5kIHRoYXQgQ291bmNpbCBpcyBtYWtpbmcgdGhlIGluZm9ybWF0aW9uIGF2YWlsYWJsZSB1bmRlciB0aGUgcHJvdmlzaW9ucyBvZiB0aGUgR0lQQSBBY3QuIDxiciAvPgo8YnIgLz4KUHJpdmFjeSBOb3RpZmljYXRpb24gLSBJbmZvcm1hdGlvbiBwcm92aWRlZCB0byBDb3VuY2lsIGluIGNvcnJlc3BvbmRlbmNlLCBzdWJtaXNzaW9ucyBvciByZXF1ZXN0cyAodmVyYmFsLCBlbGVjdHJvbmljIG9yIHdyaXR0ZW4pLCBpbmNsdWRpbmcgcGVyc29uYWwgaW5mb3JtYXRpb24gc3VjaCBhcyB5b3VyIG5hbWUgYW5kIGFkZHJlc3MsIG1heSBiZSBtYWRlIHB1YmxpY2x5IGF2YWlsYWJsZSwgaW5jbHVkaW5nIHZpYSBDb3VuY2lsIHdlYnNpdGUsIGluIGFjY29yZGFuY2Ugd2l0aCB0aGUgR292ZXJubWVudCBJbmZvcm1hdGlvbiAoUHVibGljIEFjY2VzcykgQWN0IChHSVBBIEFjdCkgMjAwOS4gQ291bmNpbCByZXNlcnZlcyB0aGUgcmlnaHQgdG8gcmVwcm9kdWNlIGluIHdob2xlIG9yIGluIHBhcnQgYW55IGNvcnJlc3BvbmRlbmNlIG9yIHN1Ym1pc3Npb24uJm5ic3A7ZAIJDxYCHwBlZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAQUaY3RsMDAkY3RNYWluJGNoa0FncmVlJGNoazGNmhzo+YGNHNog3bdr8Jbf6DdgPQ==",
                            "__VIEWSTATEGENERATOR" => "A8DC4E82",
                            "__EVENTVALIDATION" => "/wEdAATJB0NxPvcVWf/FLQLibO1VOcBhpQsJgyMjpoz897IvWkTdyBz/e/CcgfJ5zxj4jwg/VxUvaVtQChmSC3DOB0MpRrO5C68mFmt0JepQoG0l9w66+GI="
                        ];


                        $this->acceptTerms($termsUrl, $formData, $council);
                        $pdfUrl = $this->curlCheckUrl($url, [], true);
                        $baseName = str_replace([' ', '/'], '_', $docName);


                        // Delete pdf if not exists
                        if (strpos($pdfUrl['html'], '%PDF') !== false) {
//                        if(strpos($pdfUrl['html'], 'Page does not exist.') === false){
//
                            $file = fopen('pdf/' . $docId . '_=_' . $baseName . '.pdf', "w");
                            fwrite($file, $pdfUrl['html']);
                            fclose($file);
                        } else {
                            // Delete pdf
//                            $this->deletePdfById($docId);
                            echo $pdfUrl['html'] . '<Br>';
                        }
                        break;
                    case 'Inner West':
                        if (strpos($url, 'eservices.lmc.nsw.gov.au') !== false) {
                            $termsUrl = "http://eservices.lmc.nsw.gov.au/ApplicationTracking/Common/Common/terms.aspx";
                            $formData = [
                                "ctl00_rcss_TSSM" => "",
                                "ctl00_script_TSM" => "",
                                "__EVENTTARGET" => "",
                                "__EVENTARGUMENT" => "",
                                "__VIEWSTATE" => "/wEPDwUKMTQ2MzgzODU5OQ9kFgJmD2QWAgIFD2QWBAIFDxYCHgRUZXh0ZWQCCw9kFgICAQ9kFgQCAQ8WAh8ABZUZPGRpdiBzdHlsZT0iYm9yZGVyOiAwcHggc29saWQgI2RkZGRkZDsgYm9yZGVyLWltYWdlOiBub25lOyB3aWR0aDogMTAwJTsgb3ZlcmZsb3c6IHZpc2libGU7Ij4KPGgyPldlbGNvbWUgdG8gSW5uZXIgV2VzdCBDb3VuY2lsIChMZWljaGhhcmR0KSBBcHBsaWNhdGlvbiBUcmFja2luZzwvaDI+CjxkaXYgc3R5bGU9Im1hcmdpbjogMzBweDsiPgpUaGUgQXBwbGljYXRpb24gVHJhY2tpbmcgc2VydmljZSBlbmFibGVzIHRoZSBwdWJsaWMgdG8gbW9uaXRvciB0aGUgcHJvZ3Jlc3Mgb2YgdGhlIGZvbGxvd2luZyB0eXBlcyBvZiBhcHBsaWNhdGlvbnM6CjxiciAvPgo8YnIgLz4KPHRhYmxlPgogICAgPGNvbGdyb3VwPjxjb2wgc3R5bGU9IndpZHRoOiAzMyU7IiAvPgogICAgPGNvbCBzdHlsZT0id2lkdGg6IDMzJTsiIC8+CiAgICA8Y29sIHN0eWxlPSJ3aWR0aDogMzMlOyIgLz4KICAgIDwvY29sZ3JvdXA+CiAgICA8dGJvZHk+CiAgICAgICAgPHRyPgogICAgICAgICAgICA8dGQ+QWN0aXZpdHkgQXBwbGljYXRpb248L3RkPgogICAgICAgICAgICA8dGQ+RXh0ZXJuYWwgUmVmZXJyYWw8L3RkPgogICAgICAgICAgICA8dGQ+U2VjdGlvbiAzNyBTdHJhdGEgQ2VydGlmaWNhdGUgQXBwbGljYXRpb248L3RkPgogICAgICAgIDwvdHI+CiAgICAgICAgPHRyPgogICAgICAgICAgICA8dGQ+QnVpbGRpbmcgQ2VydGlmaWNhdGU8L3RkPgogICAgICAgICAgICA8dGQ+SGVyaXRhZ2UgRXhlbXB0aW9uIENlcnRpZmljYXRlPC90ZD4KICAgICAgICAgICAgPHRkPlNlY3Rpb24gOTYgTW9kaWZpY2F0aW9uIG9mIERldmVsb3BtZW50IENvbnNlbnQ8L3RkPgogICAgICAgIDwvdHI+CiAgICAgICAgPHRyPgogICAgICAgICAgICA8dGQ+Q29tcGx5aW5nIERldmVsb3BtZW50IENlcnRpZmljYXRlPC90ZD4KICAgICAgICAgICAgPHRkPk9jY3VwYXRpb24gQ2VydGlmaWNhdGU8L3RkPgogICAgICAgICAgICA8dGQ+U3ViZGl2aXNpb24gQ2VydGlmaWNhdGU8L3RkPgogICAgICAgIDwvdHI+CiAgICAgICAgPHRyPgogICAgICAgICAgICA8dGQ+Q29uc3RydWN0aW9uIENlcnRpZmljYXRlPC90ZD4KICAgICAgICAgICAgPHRkPlByZSBEQSBNZWV0aW5nPC90ZD4KICAgICAgICAgICAgPHRkPlN3aW1taW5nIFBvb2wgQ29tcGxpYW5jZSBDZXJ0aWZpY2F0ZTwvdGQ+CiAgICAgICAgPC90cj4KICAgICAgICA8dHI+CiAgICAgICAgICAgIDx0ZD5Db25zdHJ1Y3Rpb24gQ2VydGlmaWNhdGUgTW9kaWZpY2F0aW9uPC90ZD4KICAgICAgICAgICAgPHRkPlJldmlldyBvZiBEZXZlbG9wbWVudCBEZXRlcm1pbmF0aW9uPC90ZD4KICAgICAgICAgICAgPHRkPlRyZWUgQXBwbGljYWl0b248L3RkPgogICAgICAgIDwvdHI+CiAgICAgICAgPHRyPgogICAgICAgICAgICA8dGQ+RGV2ZWxvcG1lbnQgQXBwbGljYXRpb248L3RkPgogICAgICAgICAgICA8dGQ+Um9hZHMgQWN0IEFwcGxpY2F0aW9uPC90ZD4KICAgICAgICAgICAgPHRkPlRyZWUgQXBwZWFsIEFwcGxpY2F0aW9uPC90ZD4KICAgICAgICA8L3RyPgogICAgPC90Ym9keT4KPC90YWJsZT4KPC9kaXY+CjxoMj5EaXNjbGFpbWVyPC9oMj4KPGRpdiBzdHlsZT0ibWFyZ2luOiAzMHB4OyI+CklmIHlvdSBhY2Nlc3MgdGhpcyB3ZWJzaXRlIHlvdSB3aWxsIGJlIHRha2VuIHRvIGhhdmUgYWdyZWVkIHRvIHRoZSBmb2xsb3dpbmcgVGVybXMgYW5kIENvbmRpdGlvbnM6CjxiciAvPgo8YnIgLz4KVGhlIGNvbnRlbnRzIG9mIHRoaXMgd2Vic2l0ZSAod2hpY2ggaW5jbHVkZXMgZG93bmxvYWRhYmxlIG1hdGVyaWFsKSBhcmUgc3ViamVjdCB0byBjb3B5cmlnaHQgYW5kIGFyZSBwcm90ZWN0ZWQgYnkgbGF3cyBvZiBBdXN0cmFsaWEgYW5kIG90aGVyIGNvdW50cmllcyB0aHJvdWdoIGludGVybmF0aW9uYWwgdHJlYXRpZXMuCjxiciAvPgo8YnIgLz4KSW5uZXIgV2VzdCBDb3VuY2lsIGdyYW50cyB5b3UgYSBub24tZXhjbHVzaXZlIGxpY2VuY2UgdG8gcmVwcm9kdWNlIHRoZSBjb250ZW50cyBvZiB0aGlzIHdlYnNpdGUgaW4geW91ciB3ZWIgYnJvd3NlciAoYW5kIGluIGFueSBjYWNoZSBmaWxlIHByb2R1Y2VkIGJ5IHlvdXIgd2ViIGJyb3dzZXIpIGZvciB0aGUgc29sZSBwdXJwb3NlIG9mIHZpZXdpbmcgdGhlIGNvbnRlbnQuJm5ic3A7SW5uZXIgV2VzdCZuYnNwO0NvdW5jaWwgcmVzZXJ2ZXMgYWxsIG90aGVyIHJpZ2h0cy4KPGJyIC8+CjxiciAvPgpUaGUgaW5mb3JtYXRpb24gcHJvdmlkZWQgb24gdGhpcyB3ZWJzaXRlIGlzIHRvIGFzc2lzdCBjdXN0b21lcnMgaW4gdHJhY2tpbmcgdGhlIHByb2dyZXNzIG9mIEFwcGxpY2F0aW9ucy4gSXQgcmVwcmVzZW50cyBrZXkgbWlsZXN0b25lcyBpbiB0aGUgQXBwbGljYXRpb24gcHJvY2VzcyBidXQgaXMgbm90IGEgZGV0YWlsZWQgaGlzdG9yeS4gUGVyc29ucyB3aXNoaW5nIHRvIGNvbmZpcm0gaW5mb3JtYXRpb24gaW4gZGV0YWlsIHNob3VsZCBjb250YWN0IENvdW5jaWwgdmlhIGVpdGhlciB0aGUgZW1haWwgZmFjaWxpdHkgYXQgdGhlIGJvdHRvbSBvZiB0aGUgQXBwbGljYXRpb24gdHJhY2tpbmcgd2luZG93LCBvciBpbiB3cml0aW5nIGluIG9yZGVyIHRvIG9idGFpbiBhIHdyaXR0ZW4gcmVzcG9uc2UuCjxiciAvPgo8YnIgLz4KVG8gdGhlIG1heGltdW0gZXh0ZW50IHBlcm1pdHRlZCBieSBsYXcsJm5ic3A7SW5uZXIgV2VzdCZuYnNwO0NvdW5jaWwgZXhjbHVkZXMgYWxsIGxpYWJpbGl0eSB0byB5b3UgZm9yIGxvc3Mgb3IgZGFtYWdlIG9mIGFueSBraW5kIChob3dldmVyIGNhdXNlZCwgaW5jbHVkaW5nIGJ5IG5lZ2xpZ2VuY2UpIGFyaXNpbmcgZnJvbSBvciByZWxhdGluZyBpbiBhbnkgd2F5IHRvIHRoZSBjb250ZW50cyBvZiB0aGlzIHdlYnNpdGUgYW5kL29yIHlvdXIgdXNlIG9mIGl0Lgo8YnIgLz4KPGJyIC8+CkFsbCBtYXR0ZXJzIHJlbGF0aW5nIHRvIHRoaXMgd2Vic2l0ZSBhcmUgZ292ZXJuZWQgYnkgdGhlIGxhd3Mgb2YgdGhlIFN0YXRlIG9mIE5ldyBTb3V0aCBXYWxlcywgQXVzdHJhbGlhLgo8YnIgLz4KPGJyIC8+CkJ5IGFjY2Vzc2luZyB0aGlzIGluZm9ybWF0aW9uIEkgcmVxdWVzdCB0byBkbyBzbyB1bmRlciB0aGUgR292ZXJubWVudCBJbmZvcm1hdGlvbiAoUHVibGljIEFjY2VzcykgQWN0IDIwMDkgKEdJUEEgQWN0KSBhbmQgSSB1bmRlcnN0YW5kIHRoYXQgQ291bmNpbCBpcyBtYWtpbmcgdGhlIGluZm9ybWF0aW9uIGF2YWlsYWJsZSB1bmRlciB0aGUgcHJvdmlzaW9ucyBvZiB0aGUgR0lQQSBBY3QuCjwvZGl2Pgo8L2Rpdj5kAgkPFgIfAGVkGAEFHl9fQ29udHJvbHNSZXF1aXJlUG9zdEJhY2tLZXlfXxYBBRpjdGwwMCRjdE1haW4kY2hrQWdyZWUkY2hrMZ3naJv3viACgPlS5sZRh6Xu9gCb",
                                "__VIEWSTATEGENERATOR" => "88524751",
                                "__EVENTVALIDATION" => "/wEdAARp1NkhAoKaK1I3hXc8MEhSOcBhpQsJgyMjpoz897IvWkTdyBz/e/CcgfJ5zxj4jwg/VxUvaVtQChmSC3DOB0MpkNxT3w602E/WJYe1zMh6GAw3VwQ="
                            ];
                            $this->acceptTerms($termsUrl, $formData, $council);
                            $pdfUrl = $this->curlCheckUrl($url, [], true);
                            $baseName = str_replace([' ', '/'], '_', $docName);
                            // Delete pdf if not exists
                            if (strpos($pdfUrl['html'], '%PDF') !== false) {
                                $file = fopen('pdf/' . $docId . '_=_' . $baseName . '.pdf', "w");
                                fwrite($file, $pdfUrl['html']);
                                fclose($file);
                            } else {
                                echo $pdfUrl['html'] . '<Br>';
                            }
                        }
                        break;
                    case 'North Sydney':
                        $termsUrl = 'https://apptracking.northsydney.nsw.gov.au/Common/Common/terms.aspx';
                        $formData = [
                            "ctl00_rcss_TSSM" => "",
                            "ctl00_script_TSM" => "",
                            "__EVENTTARGET" => "",
                            "__EVENTARGUMENT" => "",
                            "__VIEWSTATE" => "/wEPDwUKMTQ2MzgzODU5OQ9kFgJmD2QWAgIDD2QWEAICDw8WAh4HVmlzaWJsZWhkZAIDDxYCHgRUZXh0BWM8ZGl2IGlkPSduYXYnIGNsYXNzPSdmbG9hdFJpZ2h0IGFkbWlubmF2Jz48YSBocmVmPScuLi8uLi9QYWdlcy9TZWN1cml0eS9Mb2dpbi5hc3B4Jz5Mb2dpbjwvYT48L2Rpdj5kAgQPDxYCHwBoZGQCBQ8WAh8BBZ4GPGRpdiBjbGFzcz0iaGVhZGVyIj4KPGRpdiBjbGFzcz0ibG9nbyI+CjxhIGhyZWY9Imh0dHBzOi8vd3d3Lm5vcnRoc3lkbmV5Lm5zdy5nb3YuYXUvIj4KPGltZyBhbHQ9IiIgc3JjPSJodHRwczovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L2ZpbGVzL3RlbXBsYXRlcy8wMDAwMDAwMC0wMDAwLTAwMDAtMDAwMC0wMDAwMDAwMDAwMDAvNmIyZWUyNmEtOWQ0MS00MzNkLTk3YmMtODBjMWVjNTBkMmJiL2xvZ28uZ2lmIiAvPjwvYT48L2Rpdj4KPGRpdiBjbGFzcz0idG9wLWxpbmstd3JhcHBlciI+CjxkaXYgY2xhc3M9InJpZ2h0IGJ1dHRvbi13cmFwIj48YSBocmVmPSJodHRwczovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L0luZm9ybWF0aW9uX1BhZ2VzL1JTU19GZWVkcyIgY2xhc3M9InJzcy1idXR0b24iPgo8L2E+CjwvZGl2Pgo8ZGl2IGNsYXNzPSJsZWZ0Ij4KPGEgaHJlZj0iaHR0cHM6Ly93d3cubm9ydGhzeWRuZXkubnN3Lmdvdi5hdS9Db3VuY2lsX01lZXRpbmdzL091cl9PcmdhbmlzYXRpb24vQ29udGFjdF9VcyI+Q29udGFjdCBVczwvYT4gfCA8YSBocmVmPSJodHRwczovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L0NvdW5jaWxfTWVldGluZ3MvQ291bmNpbF9OZXdzL0UtbmV3cyI+CkUtbmV3czwvYT4gfCA8YSBocmVmPSIjIiBpZD0ibGFyZ2VyLXRyaWdnZXIiPkxhcmdlciBUZXh0PC9hPiB8IDxhIGhyZWY9IiMiIGlkPSJzbWFsbGVyLXRyaWdnZXIiPgpTbWFsbGVyIFRleHQ8L2E+CjwvZGl2Pgo8L2Rpdj4KPGRpdiBjbGFzcz0ic2VhcmNoLXdyYXBwZXIiPgo8L2Rpdj4KPC9kaXY+ZAIIDxYCHwFlZAIMD2QWAgIBD2QWBgIBDxYCHwEFtB08ZGl2IHN0eWxlPSJwYWRkaW5nOiAzcHg7IGJvcmRlcjogMXB4IHNvbGlkICNkZGRkZGQ7IGJvcmRlci1pbWFnZTogbm9uZTsgd2lkdGg6IDk1JTsgb3ZlcmZsb3c6IHZpc2libGU7Ij4KPHA+PHN0cm9uZz5XZWxjb21lITwvc3Ryb25nPjxiciAvPgpBcHBsaWNhdGlvbiBUcmFja2luZyBhbGxvd3MgeW91IHRvIHRyYWNrIHRoZSBwcm9ncmVzcyBvZiBhIGRldmVsb3BtZW50IGFwcGxpY2F0aW9uIGZyb20gbG9kZ2VtZW50IHRvIGRldGVybWluYXRpb24uCjwvcD4KPHA+PHN0cm9uZz5UaGlzIHNlcnZpY2UgYXBwbGllcyB0byBhbGwgZGV2ZWxvcG1lbnQgYXBwbGljYXRpb25zIHN1Ym1pdHRlZCBzaW5jZSAxc3Qgb2YgQXVndXN0IDIwMDUuPC9zdHJvbmc+PC9wPgo8ZGl2IHN0eWxlPSJ0ZXh0LWFsaWduOiBsZWZ0OyI+PGEgaHJlZj0iaHR0cDovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L0J1aWxkaW5nX0RldmVsb3BtZW50L0N1cnJlbnRfREFzL0RBX0NvbnNlbnRzIiBjbGFzcz0iTGlzdExpbmsiIHRhcmdldD0iX2JsYW5rIj4mbmJzcDsmbmJzcDsmbmJzcDsmZ3Q7IFZpZXcgdGhlIEFwcHJvdmVkIERldmVsb3BtZW50IENvbnNlbnRzLjwvYT48L2Rpdj4KPHA+QmVmb3JlIHByb2NlZWRpbmcsIHBsZWFzZSByZWFkIHRoZSBmb2xsb3dpbmcgVGVybXMgYW5kIENvbmRpdGlvbnMgYW5kIHNlbGVjdCBhIGJ1dHRvbiBhdCB0aGUgYm90dG9tIG9mIHRoZSBwYWdlLjwvcD4KPHRhYmxlIHdpZHRoPSI5OCUiIGFsaWduPSJjZW50ZXIiIGJvcmRlcj0iMSI+CiAgICA8dGJvZHk+CiAgICAgICAgPHRyIHZhbGlnbj0idG9wIj4KICAgICAgICAgICAgPHRkIHN0eWxlPSJ3aWR0aDogMTAwJTsiPjxzdHJvbmc+PGVtPjxzcGFuIHN0eWxlPSJjb2xvcjogI2ZmMDAwMDsiPlRlcm1zIGFuZAogICAgICAgICAgICBDb25kaXRpb25zPC9zcGFuPjwvZW0+PC9zdHJvbmc+PC90ZD4KICAgICAgICA8L3RyPgogICAgICAgIDx0ciB2YWxpZ249InRvcCI+CiAgICAgICAgICAgIDx0ZCBzdHlsZT0id2lkdGg6IDEwMCU7Ij5JZiB5b3UgYWNjZXNzIHRoaXMKICAgICAgICAgICAgd2Vic2l0ZSB5b3Ugd2lsbCBiZSB0YWtlbiB0byBoYXZlIGFncmVlZCB0byB0aGUgZm9sbG93aW5nCiAgICAgICAgICAgIFRlcm1zIGFuZCBDb25kaXRpb25zOjxiciAvPgogICAgICAgICAgICA8YnIgLz4KICAgICAgICAgICAgVGhlIGNvbnRlbnRzIG9mIHRoaXMgd2Vic2l0ZSAod2hpY2ggaW5jbHVkZXMKICAgICAgICAgICAgZG93bmxvYWRhYmxlIG1hdGVyaWFsKSBhcmUgc3ViamVjdCB0byBjb3B5cmlnaHQgYW5kIGFyZQogICAgICAgICAgICBwcm90ZWN0ZWQgYnkgbGF3cyBvZiBBdXN0cmFsaWEgYW5kIG90aGVyIGNvdW50cmllcyB0aHJvdWdoCiAgICAgICAgICAgIGludGVybmF0aW9uYWwgdHJlYXRpZXMuCiAgICAgICAgICAgIDxiciAvPgogICAgICAgICAgICA8YnIgLz4KICAgICAgICAgICAgTm9ydGggU3lkbmV5IENvdW5jaWwgZ3JhbnRzIHlvdSBhIG5vbi1leGNsdXNpdmUgbGljZW5jZSB0bwogICAgICAgICAgICByZXByb2R1Y2UgdGhlIGNvbnRlbnRzIG9mIHRoaXMgd2Vic2l0ZSBpbiB5b3VyIHdlYiBicm93c2VyCiAgICAgICAgICAgIChhbmQgaW4gYW55IGNhY2hlIGZpbGUgcHJvZHVjZWQgYnkgeW91ciB3ZWIgYnJvd3NlcikgZm9yIHRoZQogICAgICAgICAgICBzb2xlIHB1cnBvc2Ugb2YgPHNwYW4gc3R5bGU9InRleHQtZGVjb3JhdGlvbjogdW5kZXJsaW5lOyI+dmlld2luZzwvc3Bhbj4gdGhlIGNvbnRlbnQuIE5vcnRoIFN5ZG5leSBDb3VuY2lsCiAgICAgICAgICAgIHJlc2VydmVzIGFsbCBvdGhlciByaWdodHMuCiAgICAgICAgICAgIDxiciAvPgogICAgICAgICAgICA8YnIgLz4KICAgICAgICAgICAgVGhlIGluZm9ybWF0aW9uIHByb3ZpZGVkIG9uIHRoaXMgd2Vic2l0ZSBpcyB0byBhc3Npc3QKICAgICAgICAgICAgY3VzdG9tZXJzIGluIHRyYWNraW5nIHRoZSBwcm9ncmVzcyBvZiB0aGUgRGV2ZWxvcG1lbnQKICAgICAgICAgICAgQXBwbGljYXRpb24uIEl0IHJlcHJlc2VudHMga2V5IG1pbGVzdG9uZXMgaW4gdGhlIERldmVsb3BtZW50CiAgICAgICAgICAgIEFwcGxpY2F0aW9uIHByb2Nlc3MgYnV0IGlzIG5vdCBhIGRldGFpbGVkIGhpc3RvcnkuIFBlcnNvbnMKICAgICAgICAgICAgd2lzaGluZyB0byBjb25maXJtIGluZm9ybWF0aW9uIGluIGRldGFpbCBzaG91bGQgY29udGFjdAogICAgICAgICAgICBDb3VuY2lsIHZpYSBlaXRoZXIgdGhlIGVtYWlsIGZhY2lsaXR5IGF0IHRoZSBib3R0b20gb2YKICAgICAgICAgICAgRGV2ZWxvcG1lbnQgQXBwbGljYXRpb24gdHJhY2tpbmcgd2luZG93LCBvciBpbiB3cml0aW5nIGluCiAgICAgICAgICAgIG9yZGVyIHRvIG9idGFpbiBhIHdyaXR0ZW4gcmVzcG9uc2UuCiAgICAgICAgICAgIDxiciAvPgogICAgICAgICAgICA8YnIgLz4KICAgICAgICAgICAgVG8gdGhlIG1heGltdW0gZXh0ZW50IHBlcm1pdHRlZCBieSBsYXcsCiAgICAgICAgICAgIE5vcnRoIFN5ZG5leSBDb3VuY2lsIGV4Y2x1ZGVzIGFsbCBsaWFiaWxpdHkgdG8geW91IGZvciBsb3NzIG9yCiAgICAgICAgICAgIGRhbWFnZSBvZiBhbnkga2luZCAoaG93ZXZlciBjYXVzZWQsIGluY2x1ZGluZyBieSBuZWdsaWdlbmNlKQogICAgICAgICAgICBhcmlzaW5nIGZyb20gb3IgcmVsYXRpbmcgaW4gYW55IHdheSB0byB0aGUgY29udGVudHMgb2YgdGhpcwogICAgICAgICAgICB3ZWJzaXRlIGFuZC9vciB5b3VyIHVzZSBvZiBpdC4KICAgICAgICAgICAgPGJyIC8+CiAgICAgICAgICAgIDxiciAvPgogICAgICAgICAgICBBbGwgbWF0dGVycyByZWxhdGluZyB0byB0aGlzIHdlYnNpdGUgYXJlCiAgICAgICAgICAgIGdvdmVybmVkIGJ5IHRoZSBsYXdzIG9mIHRoZSBTdGF0ZSBvZiBOZXcgU291dGggV2FsZXMsCiAgICAgICAgICAgIEF1c3RyYWxpYS4KICAgICAgICAgICAgPGJyIC8+CiAgICAgICAgICAgIDxiciAvPgogICAgICAgICAgICBCeQogICAgICAgICAgICBhY2Nlc3NpbmcgdGhpcyBpbmZvcm1hdGlvbiBJIHJlcXVlc3QgdG8gZG8gc28gdW5kZXIgdGhlIEdvdmVybm1lbnQKICAgICAgICAgICAgSW5mb3JtYXRpb24gKFB1YmxpYyBBY2Nlc3MpIEFjdCAyMDA5IChOU1cpIChHSVBBIEFjdCkgYW5kIEkKICAgICAgICAgICAgdW5kZXJzdGFuZCB0aGF0IENvdW5jaWwKICAgICAgICAgICAgaXMgbWFraW5nIHRoZSBpbmZvcm1hdGlvbiBhdmFpbGFibGUgdW5kZXIgdGhlIHByb3Zpc2lvbnMgb2YgdGhlCiAgICAgICAgICAgIEdvdmVybm1lbnQgSW5mb3JtYXRpb24gKFB1YmxpYyBBY2Nlc3MpIEFjdCAyMDA5IChOU1cpIChHSVBBIEFjdCkuCiAgICAgICAgICAgIDxiciAvPgogICAgICAgICAgICA8YnIgLz4KICAgICAgICAgICAgQWxsIGFwcGxpY2F0aW9ucyBieSBtZW1iZXJzIG9mIHRoZSBwdWJsaWMgdG8gdmlldyBDb3VuY2lsJ3MgcmVjb3JkcyBhcmUgc3ViamVjdCB0byB0aGUKICAgICAgICAgICAgcHJvdmlzaW9ucyBvZiBDb3VuY2lsJ3MgUHJpdmFjeSBNYW5hZ2VtZW50IFBsYW4sIFNlY3Rpb24gMTggR292ZXJubWVudCBJbmZvcm1hdGlvbiAoUHVibGljIEFjY2VzcykKICAgICAgICAgICAgQWN0IDIwMDkgJmFtcDsgU2NoZWR1bGUgMSAtIEdvdmVybm1lbnQgSW5mb3JtYXRpb24gKFB1YmxpYyBBY2Nlc3MpIFJlZ3VsYXRpb24gMjAwOS4KICAgICAgICAgICAgPC90ZD4KICAgICAgICA8L3RyPgogICAgPC90Ym9keT4KPC90YWJsZT4KPC9kaXY+CjxiciAvPgpJIGFncmVlIHRvIHRoZSBhYm92ZSB0ZXJtcyBhbmQgY29uZGl0aW9uczxiciAvPmQCBw8PFgIfAGhkZAIJDxYCHwFlZAINDxYCHwEFxwsKPGRpdiBjbGFzcz0icmlnaHQiIHN0eWxlPSJ3aWR0aDogMTIwcHg7Ij4KPGEgY2xhc3M9ImZhY2Vib29rLWxpbmsgcmlnaHQiIHRhcmdldD0iX2JsYW5rIiBocmVmPSJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9OdGhTeWRDb3VuY2lsIj4KPC9hPjxhIGNsYXNzPSJ0d2l0dGVyLWxpbmsgcmlnaHQiIHRhcmdldD0iX2JsYW5rIiBocmVmPSJodHRwOi8vdHdpdHRlci5jb20vTnRoU3lkQ291bmNpbCI+CjwvYT48YSBjbGFzcz0iaW5zdGFncmFtLWxpbmsgcmlnaHQiIHRhcmdldD0iX2JsYW5rIiBocmVmPSJodHRwOi8vd3d3Lmluc3RhZ3JhbS5jb20vbm9ydGguc3lkbmV5Ij48L2E+CjwvZGl2Pgo8ZGl2IGNsYXNzPSJjb2wtMSBsZWZ0Ij4KPGgzPgpNYWluIE9mZmljZTwvaDM+CjIwMCBNaWxsZXIgU3RyZWV0CjxiciAvPgpOb3J0aCBTeWRuZXkgMjA2MAo8YnIgLz4KVGVsZXBob25lOiAoMDIpIDk5MzYgODEwMAo8YnIgLz4KRmF4OiAoMDIpIDk5MzYgODE3NzwvZGl2Pgo8ZGl2IGNsYXNzPSJjb2wtMiBsZWZ0Ij4KPGgzPgpTdGFudG9uIExpYnJhcnk8L2gzPgoyMzQgTWlsbGVyIFN0cmVldAo8YnIgLz4KTm9ydGggU3lkbmV5IDIwNjAKPGJyIC8+ClRlbGVwaG9uZTogKDAyKSA5OTM2IDg0MDAKPC9kaXY+CjxkaXYgY2xhc3M9ImNvbC0zIGxlZnQiPgo8aDM+Ck5vcnRoIFN5ZG5leSBPdmFsPC9oMz4KNSBGaWcgVHJlZSBMYW5lCjxiciAvPgpOb3J0aCBTeWRuZXkgMjA2MAo8YnIgLz4KVGVsZXBob25lOiAoMDIpIDk5MzYgODU4NQo8L2Rpdj4KPGRpdiBjbGFzcz0iY29sLTQgbGVmdCI+CjxoMz4KTm9ydGggU3lkbmV5IE9seW1waWMgUG9vbDwvaDM+CjQgQWxmcmVkIFN0cmVldCBTb3V0aAo8YnIgLz4KTWlsc29ucyBQb2ludCAyMDYxCjxiciAvPgpUZWxlcGhvbmU6ICgwMikgOTk1NSAyMzA5CjwvZGl2Pgo8ZGl2IGNsYXNzPSJjbGVhciI+CjwvZGl2Pgo8ZGl2IGNsYXNzPSJmb290ZXItbGluay13cmFwIj4KPGEgaHJlZj0iaHR0cHM6Ly93d3cubm9ydGhzeWRuZXkubnN3Lmdvdi5hdS9JbmZvcm1hdGlvbl9QYWdlcy9Qcml2YWN5Ij5Qcml2YWN5PC9hPgp8IDxhIGhyZWY9Imh0dHBzOi8vd3d3Lm5vcnRoc3lkbmV5Lm5zdy5nb3YuYXUvSW5mb3JtYXRpb25fUGFnZXMvRGlzY2xhaW1lciI+RGlzY2xhaW1lcjwvYT4KfCA8YSBocmVmPSJodHRwczovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L0luZm9ybWF0aW9uX1BhZ2VzL0NvcHlyaWdodCI+Q29weXJpZ2h0PC9hPgp8IDxhIGhyZWY9Imh0dHBzOi8vd3d3Lm5vcnRoc3lkbmV5Lm5zdy5nb3YuYXUvSW5mb3JtYXRpb25fUGFnZXMvQS1aX0xpc3RpbmciPkEtWiBMaXN0PC9hPgp8IDxhIGhyZWY9Imh0dHBzOi8vd3d3Lm5vcnRoc3lkbmV5Lm5zdy5nb3YuYXUvSW5mb3JtYXRpb25fUGFnZXMvU2l0ZW1hcCI+U2l0ZW1hcDwvYT4KPC9kaXY+CjxkaXYgY2xhc3M9ImZhZGVkLXRleHQiPgomY29weTsgTm9ydGggU3lkbmV5IENvdW5jaWwgMjAxNi8xNwo8L2Rpdj5kAg4PFgIfAQWJFjxzdHlsZSB0eXBlPSd0ZXh0L2Nzcyc+QGltcG9ydCB1cmwoJ2h0dHBzOi8vd3d3Lm5vcnRoc3lkbmV5Lm5zdy5nb3YuYXUvZmlsZXMvdGVtcGxhdGVzLzAwMDAwMDAwLTAwMDAtMDAwMC0wMDAwLTAwMDAwMDAwMDAwMC82YjJlZTI2YS05ZDQxLTQzM2QtOTdiYy04MGMxZWM1MGQyYmIvc3R5bGUuY3NzJyk7CkBpbXBvcnQgdXJsKCdodHRwczovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L2ZpbGVzL3RlbXBsYXRlcy8wMDAwMDAwMC0wMDAwLTAwMDAtMDAwMC0wMDAwMDAwMDAwMDAvNmIyZWUyNmEtOWQ0MS00MzNkLTk3YmMtODBjMWVjNTBkMmJiL2NvbW1vbi5jc3MnKTsKQGltcG9ydCB1cmwoJ2h0dHBzOi8vd3d3Lm5vcnRoc3lkbmV5Lm5zdy5nb3YuYXUvZmlsZXMvdGVtcGxhdGVzLzAwMDAwMDAwLTAwMDAtMDAwMC0wMDAwLTAwMDAwMDAwMDAwMC82YjJlZTI2YS05ZDQxLTQzM2QtOTdiYy04MGMxZWM1MGQyYmIvZGF0YS1saXN0LXZpZXcuY3NzJyk7CkBpbXBvcnQgdXJsKCdodHRwczovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L2ZpbGVzL3RlbXBsYXRlcy8wMDAwMDAwMC0wMDAwLTAwMDAtMDAwMC0wMDAwMDAwMDAwMDAvNmIyZWUyNmEtOWQ0MS00MzNkLTk3YmMtODBjMWVjNTBkMmJiL21haW5tZW51LmNzcz9zZWFtbGVzc3RzPTYzNjQ1MzAyOTg2MDU4NDk0OScpOwpAaW1wb3J0IHVybCgnaHR0cHM6Ly93d3cubm9ydGhzeWRuZXkubnN3Lmdvdi5hdS9maWxlcy90ZW1wbGF0ZXMvMDAwMDAwMDAtMDAwMC0wMDAwLTAwMDAtMDAwMDAwMDAwMDAwLzZiMmVlMjZhLTlkNDEtNDMzZC05N2JjLTgwYzFlYzUwZDJiYi9zaXRlLmNzcz92PTEuMScpOwpAaW1wb3J0IHVybCgnaHR0cHM6Ly93d3cubm9ydGhzeWRuZXkubnN3Lmdvdi5hdS9maWxlcy90ZW1wbGF0ZXMvMDAwMDAwMDAtMDAwMC0wMDAwLTAwMDAtMDAwMDAwMDAwMDAwLzZiMmVlMjZhLTlkNDEtNDMzZC05N2JjLTgwYzFlYzUwZDJiYi9mb3JtLXRlbXBsYXRlLmNzcycpOwpAaW1wb3J0IHVybCgnaHR0cHM6Ly93d3cubm9ydGhzeWRuZXkubnN3Lmdvdi5hdS9maWxlcy90ZW1wbGF0ZXMvMDAwMDAwMDAtMDAwMC0wMDAwLTAwMDAtMDAwMDAwMDAwMDAwLzZiMmVlMjZhLTlkNDEtNDMzZC05N2JjLTgwYzFlYzUwZDJiYi93bkdhbGxlcnkuY3NzJyk7CkBpbXBvcnQgdXJsKCdodHRwczovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L2ZpbGVzL3RlbXBsYXRlcy8wMDAwMDAwMC0wMDAwLTAwMDAtMDAwMC0wMDAwMDAwMDAwMDAvNmIyZWUyNmEtOWQ0MS00MzNkLTk3YmMtODBjMWVjNTBkMmJiL0NhbGVuZGFyLmNzcycpOwovKkBpbXBvcnQgdXJsKCdodHRwczovL3d3dy5ub3J0aHN5ZG5leS5uc3cuZ292LmF1L2ZpbGVzL3RlbXBsYXRlcy8wMDAwMDAwMC0wMDAwLTAwMDAtMDAwMC0wMDAwMDAwMDAwMDAvNmIyZWUyNmEtOWQ0MS00MzNkLTk3YmMtODBjMWVjNTBkMmJiL2pxdWVyeS11aS0xLjkuMC5jdXN0b20uY3NzJyk7CgovKiBTdGFuZGFyZCBEZWZhdWx0IE1hc3RlciBTdHlsaW5nICovCgojbWFpbmxpbmUge2hlaWdodDo0MXB4ICFpbXBvcnRhbnQ7IGJhY2tncm91bmQtY29sb3I6I0YzRjNGMyAhaW1wb3J0YW50O30KI21haW5saW5lIHtiYWNrZ3JvdW5kOiBub25lO2JvcmRlcjpub25lICFpbXBvcnRhbnQ7fQojYmQuY3VzdG9tTWFzdGVyLUJvZHkge2JvcmRlci10b3A6bm9uZSAhaW1wb3J0YW50O30KI21pZC1tZW51Lm1lbnUge21hcmdpbi1sZWZ0OjA7fQoKcC5tYWluLWZvb3Rlci1wYXJhIHttYXJnaW4tYm90dG9tOiAxcHg7IHBhZGRpbmctYm90dG9tOjA7fQoKI2hkIHttaW4taGVpZ2h0OjIwNHB4ICFpbXBvcnRhbnQ7YmFja2dyb3VuZC1jb2xvcjp0cmFuc3BhcmVudCAhaW1wb3J0YW50O30KI25hdndyYXAge2JhY2tncm91bmQ6IG5vbmU7Ym9yZGVyLXRvcDpub25lO2hlaWdodDoxNXB4O30KCmltZyN0MWxvZ28ge2Rpc3BsYXk6bm9uZTt9CgouY3VzdG9tTWFzdGVyLUhlYWRlciA+IGRpdiA+IC5jb250YWluZXJMZWZ0IHsgd2lkdGg6MTAwJTt9CiNtYWlubGluZS5jdXN0b21NYXN0ZXItSGVhZGVyID4gLmFkbWluYmFubmVyIHtkaXNwbGF5Om5vbmU7fQoKYm9keSB7YmFja2dyb3VuZC1jb2xvcjogIzY0NTc0RiAhaW1wb3J0YW50O30KI2RvY2Yge2JvcmRlci1sZWZ0OiBub25lICFpbXBvcnRhbnQ7IGJvcmRlci1yaWdodDpub25lICFpbXBvcnRhbnQ7IHdpZHRoOjEwMDBweCAhaW1wb3J0YW50O30KCiNjdGwwMF9jdE1haW5fbWFpbiA+ICNjb250ZW50IHt3aWR0aDo4MDBweCAhaW1wb3J0YW50O21hcmdpbjowICFpbXBvcnRhbnQ7fQojY3RsMDBfY3RNYWluX2N0cmxMb2dpbl9wbmxVc2VyTG9naW4gPiBkaXYgPiBkaXYge3dpZHRoOjc1JSAhaW1wb3J0YW50O30KI2N0bDAwX2N0TWFpbl9jdHJsTG9naW5fcG5sVXNlckxvZ2luID4gZGl2ID4gZGl2I2N0bDAwX2N0TWFpbl9jdHJsTG9naW5fY3RybFNvY2lhbExvZ2luIHt3aWR0aDoxJSAhaW1wb3J0YW50O2Rpc3BsYXk6bm9uZSAhaW1wb3J0YW50O30KCi8qIE5TQyBDVVNUT01JU0FUSU9OIDIwLzA5LzIwMTcgKi8KaW5wdXQuYnV0dG9uQWRkW3R5cGU9c3VibWl0XSB7YmFja2dyb3VuZC1jb2xvcjojQ0FEQjQ2O30KaW5wdXQuYnV0dG9uQWRkW3R5cGU9c3VibWl0XTpob3ZlciB7YmFja2dyb3VuZC1jb2xvcjojOEVDNzRDO30KaW5wdXQuYnV0dG9uTW9kaWZ5W3R5cGU9c3VibWl0XSB7YmFja2dyb3VuZC1jb2xvcjojQ0FEQjQ2O30KaW5wdXQuYnV0dG9uTW9kaWZ5W3R5cGU9c3VibWl0XTpob3ZlciB7YmFja2dyb3VuZC1jb2xvcjojOEVDNzRDO30KCgouTGlua3NTZWN0aW9uIHsKICAgIG1hcmdpbi1sZWZ0OiAxMHB4ICFpbXBvcnRhbnQ7Cn0KIDwvc3R5bGU+ZGTj+v6F6dhYuy5gzWTtXR3Hlf01SQ==",
                            "__VIEWSTATEGENERATOR" => "A8DC4E82",
                            "__EVENTVALIDATION" => "/wEdAAKqmugmnUXloXjg4Let5Zp7RN3IHP978JyB8nnPGPiPCFzkXavTunTaE7w3tWoAzY1XErC0"
                        ];


                        $acceptedTerms = $this->acceptTerms($termsUrl, $formData, $council);

                        $pdfUrl = $this->curlCheckUrl($url, [], true);
                        $baseName = str_replace([' ', '/'], '_', $docName);
                        $file = fopen('pdf/' . $docId . '_=_' . $baseName . '.pdf', "w");
                        fwrite($file, $pdfUrl['html']);
                        fclose($file);
                        break;
                    default:
                        $pdfUrl = $this->curlCheckUrl($url, $header);
                        if (isset($pdfUrl['error'])) {
                            $this->setErrorMessage($docId, $pdfUrl['error']);

                        }
                        break;
                }


                if ($pdfUrl['url'] != false) {
//                    $file = fopen('pdf/test'.$docId.'.html', "w");
//                    fwrite($file, $pdfUrl['html']);
//                    fclose($file);

                    // Delete pdf if not exists
                    if (strpos($pdfUrl['html'], 'Requested file does not exist.') === false) {
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
                                $baseName = str_replace([' ', '/'], '_', $docName);
                                $path = 'pdf/' . $docId . '_=_' . $this->clean($baseName);
                                break;

                        }
                    } else {
                        // Delete pdf
                        $this->deletePdfById($docId);
                        echo $pdfUrl['html'] . '<Br>';
                    }

                    if ($path != '') {
                        if ($council != 'Penrith' && $council != 'Willoughby' && $council != 'Inner West' && $council != 'North Sydney') {
                            if (!strpos($path, '.doc') && !strpos($path, '.DOC')) {
                                $path = (!strpos($path, '.pdf') && !strpos($path, '.PDF') ? $path . '.pdf' : $path);
                            }
                            $fopen = @fopen($pdfUrl, 'r');
                            if ($fopen !== false) {
                                file_put_contents($path, $fopen);
                            } else {
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


    public function deletePdfById($id)
    {
        $dd = DasDocuments::findFirst([
            'conditions' => 'id = ' . $id
        ]);
        if ($dd) {
            $dd->delete();
        }

        return true;
    }

    public function setErrorMessage($id, $message)
    {
        $dd = DasDocuments::findFirst([
            'conditions' => 'id = ' . $id
        ]);
        if ($dd) {
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
                $this->updateDocStatus($docId, true, true, $config->as3->endPoint . $files[$x]);

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
            if ($as3Link != '') {
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


        if ($part == 1) {
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
                    $pdfFileName = $row->getName() . '.pdf';
                    $path = 'pdf-zip/' . str_replace(['/', '\\'], '_', $pdfFileName);
                    if ($path != '') {
                        $filesToZip[] = [
                            'path' => $path,
                            'url' => $url
                        ];
                    }

                }
            }
            echo json_encode($filesToZip);
        } elseif ($part == 2) {
            $index = $this->request->getPost('index');
            $filesToZip = $this->request->getPost('file');
            file_put_contents($filesToZip['path'], fopen($filesToZip['url'], 'r'));
            echo json_encode([
                'nextIndex' => $index + 1
            ]);
        } else {
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
        if ($useCookie == true) {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 400,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_FOLLOWLOCATION => 1, // follow redirects
                CURLOPT_AUTOREFERER => 1, // set referer on redirect
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_COOKIEFILE => $this->config->directories->cookiesDir . 'cookies.txt',
                CURLOPT_COOKIEJAR => $this->config->directories->cookiesDir . 'cookies.txt'
            ));
        } else {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 400,
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


    public function getAspFormDataByUrl($url)
    {

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


    public function getAspFormDataByString($string)
    {

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

    public function acceptTerms($url, $formData, $council, $requestHeaders = [])
    {

        switch ($council) {
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
                    "Host: eplanning.willoughby.nsw.gov.au",
                    "Origin: https://eplanning.willoughby.nsw.gov.au",
                    "Referer: https://eplanning.willoughby.nsw.gov.au/Common/Common/terms.aspx",
                    "Content-Length: " . strlen($formData)
                ];
                break;
            case 'Inner West':
                if (strpos($url, 'eservices.lmc.nsw.gov.au') !== false) {

                    $formData["__EVENTTARGET"] = null;
                    $formData["__EVENTARGUMENT"] = null;
                    $formData['ctl00$ctMain$BtnAgree'] = "I Agree";

                    $formData = http_build_query($formData);

                    $requestHeaders = [
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
                        "Accept-Encoding: gzip, deflate",
                        "Content-Type: application/x-www-form-urlencoded",
                        "Content-Length: " . strlen($formData),
                        "Host: eservices.lmc.nsw.gov.au",
                        "Origin: http://eservices.lmc.nsw.gov.au",
                        "Referer: http://eservices.lmc.nsw.gov.au/ApplicationTracking/Common/Common/terms.aspx",
                    ];

                }
                break;
            case 'North Sydney':
                // Add extra values
//                $formData["ctl00_rcss_TSSM"] = null;
                $formData["ctl00_script_TSM"] =";;System.Web.Extensions, Version=4.0.0.0, Culture=neutral, PublicKeyToken=31bf3856ad364e35:en:8f95decb-d716-4257-bc42-c772df7173e5:ea597d4b:b25378d2";
                $formData["__EVENTTARGET"] = null;
                $formData["__EVENTARGUMENT"] = null;
                $formData['ctl00$ctMain$BtnAgree'] = "I Agree";
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