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



}
