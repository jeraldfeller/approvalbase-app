<?php

namespace Aiden\Models;

class _BaseModel extends \Phalcon\Mvc\Model {
    public static function cleanString($string) {

        $string = html_entity_decode($string, ENT_QUOTES);
        $string = str_replace("\xc2\xa0", ' ', $string);
        $string = trim($string);
        $string = preg_replace('!\s+!', ' ', $string);

        return $string;

    }


    public function saveEstimatedCost($da, $estimatedCost) {

        $estimatedCost = filter_var($estimatedCost, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $estimatedCost = floatval($estimatedCost);

        if ($da->getEstimatedCost() == $estimatedCost) {

            echo " Estimated cost did not change, ignoring... <br>";
            return true;
        }
        else {

            if ($estimatedCost > 0) {

                $da->setEstimatedCost($estimatedCost);
                if ($da->save()) {


                   echo "Created estimated cost $estimatedCost <br>";
                    return true;
                }
                else {



                    echo " Error creating estimated cost $estimatedCost <br>";
                    print_r($da->getMessages(), true);
                    return false;
                }
            }
        }

        return false;

    }


    /**
     * Creates a document related to a development application
     * @param type $da
     * @param type $name
     * @param type $url
     * @param type $date
     * @return boolean
     */
    public function saveDocument($da, $name, $url, $date = null) {

        $daDocument = DasDocuments::createIfNotExists($da->getId(), $name, $url, $date);
        switch ($daDocument) {

            case DasDocuments::DOCUMENT_SAVED:

                echo "Created related document  . $name . '<br>";
                return true;

            case DasDocuments::DOCUMENT_EXISTS:


                echo "Document $name already exists, ignoring...<br>";
                return true;

            case DasDocuments::DOCUMENT_ERROR_SAVING:
             //   $this->logger->error(" Error creating related document [{document_name}]", ["document_name" => $name]);

                echo " Error creating related document $name <br>";
                return false;

            case DasDocuments::DOCUMENT_NO_NAME:
           //     $this->logger->error(" Error creating related document [{document_name}], no name", ["document_name" => $name]);
                return false;
//
            case DasDocuments::DOCUMENT_NO_URL:
//                $this->logger->error(" Error creating related document [{document_name}], no URL", ["document_name" => $name]);
                return false;
        }

    }


    public function saveLodgeDate($da, $date) {

        if (($date instanceof \DateTime) === false) {
            echo "Passed $date [{date}] was not instanceof \DateTime <br>";
            return false;
        }

        $oldLodgeDate = $da->getLodgeDate();
        $date->setTime(0, 0, 0);


        if (is_a($oldLodgeDate, "DateTime") && $date->format("Y-m-d") === $oldLodgeDate->format("Y-m-d")) {

            echo "Date did not change, skipping...\n";
            return true;
        }
        else {

            if ($date !== false && $oldLodgeDate !== $date) {

                $da->setLodgeDate($date);
                if ($da->save()) {
                    echo "Created lodge date ".$date->format("r")." \n";
                    return true;
                }
                else {
                    echo "Error creating lodge date ".$date->format("r")." \n";
                    return false;
                }
            }
        }

    }


    /**
     * Creates an address related to a development application
     * @param type $da
     * @param type $address
     * @return boolean
     */
    public function saveAddress($da, $address) {

        $daAddress = DasAddresses::createIfNotExists($da->getId(), $address);

        switch ($daAddress) {

            case DasAddresses::ADDRESS_CREATED:
                echo "Created related address $address <br>";
                return true;

            case DasAddresses::ADDRESS_ERROR_ON_SAVE:
                echo "Error creating related address $address <br>";
                return false;

            case DasAddresses::ADDRESS_EXISTS:
                echo "Address $address did not change, skipping...";
                return true;

            case DasAddresses::ADDRESS_ZERO_LENGTH:
                echo " Address with zero length detected (error ignored, address may be added later)... <br>";
                return true;
        }

    }

    /**
     * Creates a description related to a development application
     * @param type $da
     * @param type $newDescription
     * @return boolean
     */
    public function saveDescription($da, $newDescription) {

        $oldDescription = $da->getDescription();

        if ($oldDescription === $newDescription) {
            echo " Description didn't change, skipping... <br>";
            return true;
        }
        else {

            $da->setDescription($newDescription);
            if ($da->save()) {

                echo "Created description $newDescription <br>";
                return true;
            }
            else {
                echo " Error creating description $newDescription <br>";
                return false;
            }
        }

    }

    /**
     * Creates a party related to a development application
     * @param type $da
     * @param type $role
     * @param type $name
     * @return boolean
     */
    public function saveParty($da, $role, $name) {

        $dasParty = DasParties::createIfNotExists($da->getId(), $role, $name);
        switch ($dasParty) {

            case DasParties::PARTY_SAVED:
                echo "Created related party $role with value $name <br>";
                return true;

            case DasParties::PARTY_ERROR_SAVING:
                echo "Error creating related party $role with value $name <br>";
                return false;

            case DasParties::PARTY_NO_NAME:

                echo "Error creating related party $role with value $name, no name <br>";
                return false;

            case DasParties::PARTY_EXISTS:

                echo "Related party $role with value $name already exists, ignoring...<br>";
                return true;
        }

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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0');

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);

        curl_close($ch);
        // No errors
        if ($errno !== 0) {
            var_dump($errmsg);
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

    public function scrapeTo($url,  $showInfo = false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../cookies/cookies.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0');
        if($showInfo == true){
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        }

        $output = curl_exec($ch);
        if($showInfo == true){
            $information = curl_getinfo($ch);
        }
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);

        if ($errno !== 0) {
            echo "cURL error: $errmsg $errno <br>";
            return false;
        }

        if($showInfo == true){
            return [
                'output' => $output,
                'info' => $information
            ];
        }else{
            return str_get_html($output);
        }

    }


}
