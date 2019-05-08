<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 12/10/2018
 * Time: 5:44 AM
 */

namespace Aiden\Models;

use Aiden\Models\DasAddresses;
use Aiden\Models\Das;

class Poi extends _BaseModel
{
    const TYPE_PRIMARY = 1;
    const TYPE_SECODARY = 2;
    const TEMPLATE_DARK = 'mapbox://styles/mapbox/dark-v9';
    const TEMPLATE_LIGHT = 'mapbox://styles/mapbox/light-v9';


    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    protected $id;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $users_id;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $address;

    /**
     * @Column(type="double", nullable=true)
     */
    protected $latitude;

    /**
     * @Column(type="double", nullable=true)
     */
    protected $longitude;

    /**
     * @Column(type="double", nullable=false)
     */
    protected $radius;

    /**
     * @Column(type="double", nullable=false)
     */
    protected $min_cost;

    /**
     * @Column(type="double", nullable=false)
     */
    protected $max_cost;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $metadata;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $type;


    /**
     * @Column(type="string", nullable=true)
     */
    protected $date_created;

    /**
     * Returns the database table name
     * @return string
     */

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $processed;


    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $imported;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $imported_processed;

    public function getSource()
    {

        return 'poi';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Aiden\Models\Users', 'id', ['alias' => 'User']);
    }


    /**
     * Returns the model's unique identifier
     * @return int
     */
    public function getId()
    {

        return $this->id;

    }

    /**
     * Returns the related council's unique identifier
     * @return int
     */
    public function getUsersId()
    {

        return $this->users_id;

    }

    /**
     * Returns the related council's unique identifier
     * @return int
     */
    public function setUsersId(int $users_id)
    {

        return $this->users_id = $users_id;

    }

    /**
     * Returns the email address
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the email address
     * @return string
     */
    public function setName(string $name)
    {
        return $this->name = $name;
    }

    /**
     * Returns the email address
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Returns the email address
     * @return string
     */
    public function setAddress(string $address)
    {
        return $this->address = $address;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setLatitude(string $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setLongitude(string $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setRadius(string $radius)
    {
        $this->radius = $radius;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getMinCost()
    {
        return $this->min_cost;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setMinCost(string $min_cost)
    {
        $this->min_cost = $min_cost;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getMaxCost()
    {
        return $this->max_cost;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setMaxCost(string $max_cost)
    {
        $this->max_cost = $max_cost;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getMetadata()
    {
        return (bool)$this->metadata;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setMetadata(bool $metadata)
    {
        $this->metadata = $metadata;
    }


    /**
     * Returns the email address
     * @return double
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setDateCreated(string $date_created)
    {
        $this->date_created = $date_created;
    }


    /**
     * Returns the email address
     * @return double
     */
    public function getProcessed()
    {
        return (bool)$this->processed;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setProcessed(bool $processed)
    {
        $this->processed = $processed;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getImported()
    {
        return (bool)$this->imported;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setImported(bool $imported)
    {
        $this->imported = $imported;
    }


    public function getImportedProcessed()
    {
        return (bool)$this->imported_processed;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setImportedProcessed(bool $imported_processed)
    {
        $this->imported_processed = $imported_processed;
    }


    public static function getDaRadius($lat, $lon, $rad, $minCost, $maxCost, $metadata, $type, $poiId)
    {
        // get DAS inside circle radius
        $R = 6371;

        // first-cut bounding box (in degrees)
        $maxLat = $lat + rad2deg($rad / $R);
        $minLat = $lat - rad2deg($rad / $R);
        $maxLon = $lon + rad2deg(asin($rad / $R) / cos(deg2rad($lat)));
        $minLon = $lon - rad2deg(asin($rad / $R) / cos(deg2rad($lat)));

        $sql = "Select das_id, address, latitude, longitude,
                   acos(sin(:lat)*sin(radians(latitude)) + cos(:lat)*cos(radians(latitude))*cos(radians(longitude)-:lon)) * :R As D
            From (
                Select das_id, address, latitude, longitude
                From das_addresses
                Where latitude Between :minLat And :maxLat
                  And longitude Between :minLon And :maxLon

            ) As FirstCut
            Where acos(sin(:lat)*sin(radians(latitude)) + cos(:lat)*cos(radians(latitude))*cos(radians(longitude)-:lon)) * :R < :rad
            Order by D";

        $sqlParams = [
            'lat' => deg2rad($lat),
            'lon' => deg2rad($lon),
            'minLat' => $minLat,
            'minLon' => $minLon,
            'maxLat' => $maxLat,
            'maxLon' => $maxLon,
            'rad' => $rad,
            'R' => $R,
        ];


        $fromDate = date('Y-m-d', strtotime('-6 months'));

        $da = new DasAddresses();
        $daResults = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $da
            , $da->getReadConnection()->query($sql, $sqlParams, [])
        );

        $daAddresses = [];
        foreach ($daResults as $dr) {
            // filter DA based on poi parameters

            $includeNull = ($minCost == 0 ? " OR d.estimated_cost IS NULL " : "");
            $costQuery = " AND ((d.estimated_cost >= " . $minCost . " AND d.estimated_cost <= " . $maxCost . ")" . $includeNull . ")";

            // metadata
            $metadataQuery = '';
            if ($metadata == false) {
                $metadataQuery = ' AND (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) > 1 ';
            }

            $sql = "SELECT d.id, d.council_reference, d.description, d.estimated_cost, d.lodge_date, d.created, c.name
                     FROM das d, councils c
                     WHERE d.id = " . $dr->getDasId() .
                $costQuery . $metadataQuery . " AND d.council_id = c.id
                AND d.lodge_date > '".$fromDate."'
                ORDER BY d.lodge_date DESC";

            $das = new Das();
            $dasResults = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $das
                , $das->getReadConnection()->query($sql, [], [])
            );


            foreach ($dasResults as $d) {
                // get documents
                $documents = [];
                $docs = DasDocuments::find([
                    'conditions' => 'das_id = :das_id:',
                    'bind' => [
                        'das_id' => $d->getId()
                    ]
                ]);

                foreach ($docs as $doc) {
                    $documents[] = [
                        'name' => $doc->getName(),
                        'url' => $doc->getUrl()
                    ];
                }


                if ($d->getLodgeDate() !== NULL) {
                    $currentDateTime = $d->getLodgeDate()->format('c');
                    $currentDate = $d->getLodgeDate()->format('d-m-Y');
                    $dateUnix = strtotime($d->getLodgeDate()->format('Y-m-d'));
                } else {
                    $currentDateTime = $d->getCreated()->format('c');
                    $currentDate = $d->getCreated()->format('d-m-Y');
                    $dateUnix = strtotime($d->getCreated()->format('Y-m-d'));
                }

                $date = date('Y-m-d');
                $date1 = strtotime($currentDate);
                $date2 = strtotime($date);
                $diff = abs($date2 - $date1);
                // To get the year divide the resultant date into
// total seconds in a year (365*60*60*24)
                $years = floor($diff / (365 * 60 * 60 * 24));


// To get the month, subtract it with years and
// divide the resultant date into
// total seconds in a month (30*60*60*24)
                $months = floor(($diff - $years * 365 * 60 * 60 * 24)
                    / (30 * 60 * 60 * 24));
                $days = floor(($diff - $years * 365 * 60 * 60 * 24 -
                        $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

                $uploaded = '<span class="sub-title">
                                    <i class="ti-timer pdd-right-5"></i>
                                    <span>
                                        <time class="timeago" datetime="' . $currentDateTime . '">' . $currentDate . '</time>
                                    </span>
                                </span>';


                $daAddresses[] = array(
                    'type' => $type,
                    'poiId' => $poiId,
                    'dasId' => $d->getId(),
                    'lodgeDate' => $uploaded,
                    'lodgeDateUnix' => $dateUnix,
                    'councilReference' => $d->getCouncilReference(),
                    'description' => $d->getDescription(),
                    'estimatedCost' => $d->getEstimatedCost(),
                    'address' => $dr->getAddress(),
                    'latitude' => $dr->getLatitude(),
                    'longitude' => $dr->getLongitude(),
                    'documents' => $documents,
                    'council' => $d->name,
                    'days' => $days,
                    'dotColor' => ($days > 30 ? 'white' : 'purple')
                );
            }


        }

        return $daAddresses;
    }


    public static function getDaSecondary($address, $minCost, $maxCost, $metadata, $type, $poiId)
    {

        // filter DA based on poi parameters
        $includeNull = ($minCost == 0 ? " OR d.estimated_cost IS NULL " : "");
        $costQuery = " AND ((d.estimated_cost >= " . $minCost . " AND d.estimated_cost <= " . $maxCost . ")" . $includeNull . ")";

        // metadata
        $metadataQuery = '';
        if ($metadata == false) {
            $metadataQuery = ' AND (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) > 1 ';
        }

        $fromDate = date('Y-m-d', strtotime('-6 months'));

        $sql = "SELECT d.id, d.council_reference, d.description, d.estimated_cost, d.lodge_date,
                       da.address, da.latitude, da.longitude
                     FROM das d, das_addresses da
                     WHERE d.id = da.das_id
                     AND d.lodge_date > '".$fromDate."'
                     AND da.address = '" . addslashes($address) . "'" . $costQuery . $metadataQuery;


        $das = new Das();
        $dasResults = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        $daAddresses = [];
        foreach ($dasResults as $d) {
            // get documents
            $documents = [];
            $docs = DasDocuments::find([
                'conditions' => 'das_id = :das_id:',
                'bind' => [
                    'das_id' => $d->getId()
                ]
            ]);

            foreach ($docs as $doc) {
                $documents[] = [
                    'name' => $doc->getName(),
                    'url' => $doc->getUrl()
                ];
            }


            if ($d->getLodgeDate() != null) {
                $currentDateTime = $d->getLodgeDate()->format('c');
                $currentDate = $d->getLodgeDate()->format('d-m-Y');
            } else {
                $currentDateTime = $d->getLodgeDate()->format('c');
                $currentDate = $d->getLodgeDate()->format('d-m-Y');
            }

            $uploaded = '<span class="sub-title">
                                    <i class="ti-timer pdd-right-5"></i>
                                    <span>
                                        <time class="timeago" datetime="' . $currentDateTime . '">' . $currentDate . '</time>
                                    </span>
                                </span>';


            $daAddresses[] = array(
                'type' => $type,
                'poiId' => $poiId,
                'dasId' => $d->getId(),
                'lodgeDate' => $uploaded,
                'lodgeDateUnix' => strtotime($d->getLodgeDate()->format('Y-m-d')),
                'councilReference' => $d->getCouncilReference(),
                'description' => $d->getDescription(),
                'estimatedCost' => $d->getEstimatedCost(),
                'address' => $d->address,
                'latitude' => $d->latitude,
                'longitude' => $d->longitude,
                'documents' => $documents
            );
        }

        return $daAddresses;

    }


    public static function getMetricsPoi($from, $to, $userId)
    {
        $poi = new Poi();
        $sql = "SELECT count(id) as totalCount 
                FROM poi 
                WHERE users_id = " . $userId . "
                AND (date_created >= '" . $from . "' AND date_created <= '" . $to . "')";
        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $poi
            , $poi->getReadConnection()->query($sql, [], [])
        );

        return $result[0]->totalCount;
    }


    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
    /*::                                                                         :*/
    /*::  This routine calculates the distance between two points (given the     :*/
    /*::  latitude/longitude of those points). It is being used to calculate     :*/
    /*::  the distance between two locations using GeoDataSource(TM) Products    :*/
    /*::                                                                         :*/
    /*::  Definitions:                                                           :*/
    /*::    South latitudes are negative, east longitudes are positive           :*/
    /*::                                                                         :*/
    /*::  Passed to function:                                                    :*/
    /*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
    /*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
    /*::    unit = the unit you desire for results                               :*/
    /*::           where: 'M' is statute miles (default)                         :*/
    /*::                  'K' is kilometers                                      :*/
    /*::                  'N' is nautical miles                                  :*/
    /*::                                                                         :*/
    /*::                                                                         :*/
    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
    public static function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }


    public static function createSamplePoiAction($userId)
    {
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();

        $name = 'ApprovalBase HQ';
        $address = '1 Bligh Street, Sydney 2000';
        $addr = urlencode($address);
        $radius = 0.2;
        $metadata = false;
        $costFrom = 0;
        $costTo = 9999999999;
        $type = 1;
        $date = date('Y-m-d');
        $response = self::curlToMapBox($addr, $config);
        $response = json_decode($response);
        if (count($response->features) > 0) {
            // get most relevant
            $center = $response->features[0]->center;
            $placeName = $response->features[0]->place_name;
            $long = $center[0];
            $lat = $center[1];
            $poi = new Poi();
            $poi->setUsersId($userId);
            $poi->setName($name);
            $poi->setAddress($address);
            $poi->setRadius($radius);
            $poi->setLatitude($lat);
            $poi->setLongitude($long);
            $poi->setMinCost($costFrom);
            $poi->setMaxCost($costTo);
            $poi->setMetadata($metadata);
            $poi->setType($type);
            $poi->setDateCreated($date);
            $poi->save();

        }

    }


    public static function curlToMapbox($addr, $config)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.mapbox.com/geocoding/v5/mapbox.places/" . $addr . ".json?access_token=" . $config->mapboxApiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: 40d9d6dc-6e61-47f0-9508-34b9fcf8d76b",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        }

        return $response;
    }


}