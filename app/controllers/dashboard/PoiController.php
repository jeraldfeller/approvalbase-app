<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 12/9/2018
 * Time: 1:00 PM
 */

namespace Aiden\Controllers;

use Aiden\Controllers\_BaseController;
use Aiden\Models\DasAddresses;
use Aiden\Models\Das;
use Aiden\Models\DasDocuments;
use Aiden\Models\DasPoiUsers;
use Aiden\Models\Mapbox;
use Aiden\Models\Poi;


class PoiController extends _BaseController
{
    public function indexAction()
    {


        $this->view->setVars([
            'page_title' => 'Point of Interest',
        ]);

        $this->view->pick('poi/index');
    }

    public function primaryAction()
    {
        if ($this->getUser()->level == 1) {
            if ($this->getUser()->solution != 'monitor') {
                return json_encode('error');
            }
        }

        $this->view->setVars([
            'page_title' => 'Assets (Primary)',
            'type' => Poi::TYPE_PRIMARY,
            'center' => ($this->request->getQuery("center") ? explode(',', $this->request->getQuery("center")) : $this->getDefaultCenter(Poi::TYPE_PRIMARY)),
            'dotColor' => '#6773f1',
            'template' => Poi::TEMPLATE_DARK
        ]);

        $this->view->pick('poi/index');
    }

    public function secondaryAction()
    {
        if ($this->getUser()->level == 1) {
            if ($this->getUser()->solution != 'monitor') {
                return json_encode('error');
            }
        }
        $this->view->setVars([
            'page_title' => 'Assets (Secondary)',
            'type' => Poi::TYPE_SECODARY,
            'center' => ($this->request->getQuery("center") ? explode(',', $this->request->getQuery("center")) : $this->getDefaultCenter(Poi::TYPE_PRIMARY)),
            'dotColor' => '#2f343d',
            'template' => Poi::TEMPLATE_DARK
        ]);

        $this->view->pick('poi/index');
    }


    public function alertAction()
    {
        if ($this->getUser()->level == 1) {
            if ($this->getUser()->solution != 'monitor') {
                return json_encode('error');
            }
        }
        $this->view->setVars([
            'page_title' => 'Assets (Alert)',
            'center' => $this->getDefaultCenter(Poi::TYPE_SECODARY),
            'template' => Poi::TEMPLATE_DARK,
            'status' => DasPoiUsers::STATUS_LEAD
        ]);
        $this->view->pick('poi/alert');
    }

    public function alertSavedAction()
    {
        if ($this->getUser()->level == 1) {
            if ($this->getUser()->solution != 'monitor') {
                return json_encode('error');
            }
        }
        $this->view->setVars([
            'page_title' => 'Assets (Saved)',
            'center' => $this->getDefaultCenter(Poi::TYPE_SECODARY),
            'template' => Poi::TEMPLATE_DARK,
            'status' => DasPoiUsers::STATUS_SAVED
        ]);
        $this->view->pick('poi/alert');
    }


    public function getDefaultCenter($type)
    {
        $poi = Poi::findFirst([
            'conditions' => 'users_id = :users_id: AND type = :type:',
            'bind' => [
                'users_id' => $this->getUser()->getId(),
                'type' => $type
            ]
        ]);

        if ($poi) {
            return [$poi->getLongitude(), $poi->getLatitude()];
        } else {
            return [151.1668269, -33.9009471];
        }
    }

    public function getAction()
    {


        $poi = new Poi();
        $address = $this->request->getPost('address');
        $type = $this->request->getPost('type');
        $id = $this->request->getPost('id');
        $typeQry = '';
        if (!is_array($type)) {
            $typeQry = ' AND type = ' . $type;

        }
        $addressQry = '';
        if ($address != '') {
            $addressQry = ' AND address = "' . $address . '" ';
        }
        $idQuery = '';
        if ($id != 0) {
            $idQuery = ' AND id = ' . $id;
        }
        $sql = 'Select * FROM poi WHERE users_id = ' . $this->getUser()->getId() . $addressQry . $typeQry . $idQuery;

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $poi
            , $poi->getReadConnection()->query($sql, [], [])
        );


        if ($result) {
            $data = [];
            $daAddresses = [];
            if (count($result) > 0) {
                foreach ($result as $row) {
                    $data[] = array(
                        'id' => $row->getId(),
                        'name' => ($row->getName() != '' ? $row->getName() : 'N/A'),
                        'address' => $row->getAddress(),
                        'latitude' => $row->getLatitude(),
                        'longitude' => $row->getLongitude(),
                        'radius' => $row->getRadius(),
                        'minCost' => $row->getMinCost(),
                        'maxCost' => $row->getMaxCost(),
                        'metadata' => $row->getMetadata(),
                        'type' => $row->getType()
                    );
                    if ($type == 1) {
                        if ($row->getLatitude() != 0 && $row->getLongitude() != 0) {
                            $daAddresses[] = Poi::getDaRadius($row->getLatitude(), $row->getLongitude(), $row->getRadius(), $row->getMinCost(), $row->getMaxCost(), $row->getMetadata(), $type, $row->getId());
                        }
                    } else {
                        $daAddresses[] = Poi::getDaSecondary($row->getAddress(), $row->getMinCost(), $row->getMaxCost(), $row->getMetadata(), $type, $row->getId());
                    }
                }
            }
            return json_encode(array('data' => $data, 'addresses' => $daAddresses));
        } else {
            return json_encode(false);
        }

    }

    public function getPoiAlertsAction()
    {
        $das = new Das();
        $status = $this->request->getPost('status');
        $statusQry = ($status == DasPoiUsers::STATUS_SAVED ? ' AND dp.status = ' . DasPoiUsers::STATUS_SAVED : '');
        $sql = 'SELECT d.id, d.council_reference, d.description, d.lodge_date, d.created,
                       da.address, da.latitude, da.longitude,
                       p.type, c.name, p.id as poiId,
                       dp.status
                FROM das d, das_addresses da, poi p, das_poi_users dp, councils c
                WHERE dp.das_id = d.id
                AND d.id = da.das_id 
                AND p.id = dp.users_poi_id
                AND d.council_id = c.id
                AND dp.users_id = ' . $this->getUser()->getId() . $statusQry . '
                ORDER BY d.lodge_date DESC';

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        $data = [];
        foreach ($result as $row) {

            // get documents
            $documents = [];
            $docs = DasDocuments::find([
                'conditions' => 'das_id = :das_id:',
                'bind' => [
                    'das_id' => $row->id
                ]
            ]);


            if ($row->lodge_date !== NULL) {
                $currentDateTime = $row->lodge_date->format('c');
                $currentDate = $row->lodge_date->format('d-m-Y');
                $dateUnix = strtotime($row->lodge_date->format('Y-m-d'));
            } else {
                $currentDateTime = $row->created->format('c');
                $currentDate = $row->created->format('d-m-Y');
                $dateUnix = strtotime($row->created->format('Y-m-d'));
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


            foreach ($docs as $doc) {
                $documents[] = [
                    'name' => $doc->getName(),
                    'url' => $doc->getUrl()
                ];
            }
            $data[] = array(
                'dasId' => $row->id,
                'poiId' => $row->poiId,
                'councilReference' => $row->council_reference,
                'lodgeDate' => $uploaded,
                'lodgeDateUnix' => $dateUnix,
                'address' => $row->address,
                'latitude' => $row->latitude,
                'longitude' => $row->longitude,
                'type' => $row->type,
                'councilName' => $row->name,
                'description' => $row->description,
                'documents' => $documents,
                'days' => $days,
                'status' => $row->status
            );
        }
        return json_encode($data);
    }

    public function saveAction()
    {
        $date = date('Y-m-d');
        $mode = $this->request->getPost('mode');
        $name = $this->request->getPost('name');
        $type = $this->request->getPost('type');
        $address = $this->request->getPost('address');
        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        $radius = ($type == 1 ? $this->request->getPost('radius') : 0);
        $costFrom = $this->request->getPost('costFrom');
        $costTo = $this->request->getPost('costTo');
        $metadata = ($this->request->getPost('metadata') == 'true' ? true : false);
        $coordinates = [];

        if ($mode == 'add') {
            if ($longitude == 0 && $latitude == 0) {
                $geocode = Mapbox::geocode($address);
                if ($geocode['longitude'] != 0 && $geocode['latitude'] != 0) {
                    $latitude = $geocode['latitude'];
                    $longitude = $geocode['longitude'];
                } else {
                    return json_encode(array(
                        'success' => false,
                        'message' => 'Address not found'
                    ));
                }
            }
            $poi = new Poi();
            $poi->setUsersId($this->getUser()->getId());
            $poi->setLatitude($latitude);
            $poi->setLongitude($longitude);
            $poi->setDateCreated($date);

        } else {
            $id = $this->request->getPost('id');
            $poi = Poi::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $id
                ]
            ]);

            $coordinates = [$poi->getLongitude(), $poi->getLatitude()];
            if ($address != $poi->getAddress()) {
                // new address.. get new lat, lang
                $geocode = Mapbox::geocode($address);
                if ($geocode['longitude'] != 0 && $geocode['latitude'] != 0) {
                    $latitude = $geocode['latitude'];
                    $longitude = $geocode['longitude'];
                } else {
                    return json_encode(array(
                        'success' => false,
                        'message' => 'Address not found'
                    ));
                }

                $poi->setLatitude($latitude);
                $poi->setLongitude($longitude);
                $coordinates = [$longitude, $latitude];
            }

        }

        $poi->setType($type);
        if ($name != '') {
            $poi->setName($name);
        }
        $poi->setAddress($address);
        $poi->setRadius($radius);
        $poi->setMinCost($costFrom);
        $poi->setMaxCost($costTo);
        $poi->setMetadata($metadata);

        $save = $poi->save();
        if ($save) {
            return json_encode(array('success' => true, 'coordinates' => $coordinates));
        } else {
            return json_encode(array(
                'success' => false,
                'message' => 'Ops! something went wrong, please try again.'
            ));
        }
    }


    public function deleteAction()
    {

        $id = $this->request->getPost('id');
        $poi = Poi::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);

        if ($poi->delete()) {
            $poiUsers = DasPoiUsers::find([
                'conditions' => 'users_poi_id = :users_poi_id:',
                'bind' => [
                    'users_poi_id' => $id
                ]
            ]);

            $poiUsers->delete();
        }

        return json_encode(true);
    }


    public function saveDaAction()
    {
        $dasId = $this->request->getPost('dasId');
        $poiId = $this->request->getPost('poiId');
        $status = $this->request->getPost('status');
        $userId = $this->getUser()->getId();

        $dpu = DasPoiUsers::findFirst([
            'conditions' => 'das_id = :das_id: AND users_poi_id = :users_poi_id: AND users_id = :users_id:',
            'bind' => [
                'das_id' => $dasId,
                'users_poi_id' => $poiId,
                'users_id' => $userId
            ]
        ]);

        if ($dpu) {
//            if($status == 'false'){
//                $dpu->setStatus(DasPoiUsers::STATUS_SAVED);
//            }else{
//                $dpu->setStatus(DasPoiUsers::STATUS_LEAD);
//            }
            $dpu->setStatus($status);
            $dpu->save();
        }


        return json_encode(true);
    }


    public function importAction()
    {
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();
        $date = date('Y-m-d H:i:s');
        $type = $_POST['type'];
        if (isset($_FILES['importFile']['tmp_name'])) {
            if (pathinfo($_FILES['importFile']['name'], PATHINFO_EXTENSION) == 'csv') {
                $file = $_FILES['importFile']['tmp_name'];
                $fileName = $_FILES['importFile']['name'];
                $flag = true;
                $fileHandle = fopen($_FILES['importFile']['tmp_name'], "r");
                while (($data = fgetcsv($fileHandle, 10000, ",")) !== FALSE) {
                    if ($flag) {
                        $flag = false;
                        continue;
                    }
                    $asset = trim($data[0]);
                    $address = trim($data[1]);
                    $radius = ($type == 1 ? 0.2 : 0);
                    $costFrom = 0;
                    $costTo = 9999999999;
                    $metadata = true;

                    $addr = urlencode($address);

                    // check address if exists
                    $poi = Poi::findFirst([
                        'conditions' => 'address = :address: AND type = :type: AND users_id = :usersId:',
                        'bind' => [
                            'address' => $address,
                            'type' => $type,
                            'usersId' => $this->getUser()->getId()
                        ]
                    ]);
                    if (!$poi) {
                        $response = $this->curlToMapBox($addr, $config);
                        $response = json_decode($response);
                        if (count($response->features) > 0) {
                            // get most relevant
                            $center = $response->features[0]->center;
                            $placeName = $response->features[0]->place_name;
                            $long = $center[0];
                            $lat = $center[1];
                            $poi = new Poi();
                            $poi->setUsersId($this->getUser()->getId());
                            $poi->setName($asset);
                            $poi->setAddress($address);
                            $poi->setRadius($radius);
                            $poi->setLatitude($lat);
                            $poi->setLongitude($long);
                            $poi->setMinCost($costFrom);
                            $poi->setMaxCost($costTo);
                            $poi->setMetadata($metadata);
                            $poi->setType($type);
                            $poi->setDateCreated($date);
                            $poi->setImported(true);
                            $poi->save();

                        }
                    }
                }

                fclose($fileHandle);
            }
            echo true;
        } else {
            echo false;
        }
    }


    public function curlToMapbox($addr, $config)
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