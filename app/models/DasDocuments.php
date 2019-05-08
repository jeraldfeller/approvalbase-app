<?php

namespace Aiden\Models;

use Aiden\Models\Users;
use Aiden\Models\DasUsers;

class DasDocuments extends _BaseModel {

    const DOCUMENT_NO_NAME = 1;
    const DOCUMENT_NO_URL = 2;
    const DOCUMENT_ERROR_SAVING = 3;
    const DOCUMENT_EXISTS = 4;
    const DOCUMENT_SAVED = 5;

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    protected $id;
    /**
     * @Column(type="integer", nullable=false)
     */
    protected $das_id;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $name;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $url;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $as3_url;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $date;
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $as3_processed;
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $status;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $checked;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $checked_status;


    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'das_documents';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        $this->belongsTo('das_id', 'Aiden\Models\Das', 'id', ['alias' => 'Da']);

    }

    /**
     * Returns the model's unique identifier
     * @return int
     */
    public function getId() {

        return $this->id;

    }

    /**
     * Returns the affected development application id
     * @return int
     */
    public function getDasId() {

        return $this->das_id;

    }

    /**
     * Sets the affected development application's id
     * @param int $das_id
     */
    public function setDasId(int $das_id) {

        $this->das_id = $das_id;

    }

    /**
     * Gets the name of the document
     * @return string
     */
    public function getName() {
        return $this->name;

    }

    /**
     * Sets the name of the document
     * @param string $name
     */
    public function setName(string $name) {

        $this->name = $name;

    }

    /**
     * Gets the document URL
     * @return type
     */
    public function getUrl($forceCouncilUrl = false) {

        if (strlen($this->getAs3Url()) > 0 && $forceCouncilUrl === false) {
            return $this->getAs3Url();
        }
        else {
            return $this->url;
        }

    }

    /**
     * Sets the document URL
     * @param string $url
     */
    public function setUrl(string $url) {

        $this->url = $url;

    }

    /**
     * Gets the document's AS3 URL
     * @return type
     */
    public function getAs3Url() {
        return $this->as3_url;

    }

    /**
     * Sets the document's AS3 URL
     * @param string $url
     */
    public function setAs3Url(string $as3_url) {

        $this->as3_url = $as3_url;

    }

    /**
     * Gets the document date
     * @return type
     */
    public function getDate() {

        if ($this->date === null) {
            return null;
        }
        else {

            $date = \DateTime::createFromFormat('Y-m-d', $this->date);
            return $date;
        }

    }

    /**
     * Gets the document date
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date) {

        $this->date = $date->format('Y-m-d H:i:s');

    }

    /**
     * Returns whether a document was uploaded to AS3
     */
    public function getAs3Processed() {
        return (bool) $this->as3_processed;

    }



    /**
     * Sets whether a document was uploaded to AS3
     * @param bool $as3_processed
     */
    public function setAs3Processed(bool $as3_processed) {
        $this->as3_processed = (int) $as3_processed;

    }


    /**
     * Sets whether a document was uploaded to AS3
     * @param bool $as3_processed
     */
    public function setStatus(bool $status) {
        $this->status = (int) $status;

    }

    /**
     * Returns whether a document was uploaded to AS3
     */
    public function getStatus() {
        return (bool) $this->status;

    }


    /**
     * Sets whether a document was uploaded to AS3
     * @param bool $as3_processed
     */
    public function setChecked(bool $checked) {
        $this->checked = (int) $checked;

    }

    /**
     * Returns whether a document was uploaded to AS3
     */
    public function getChecked() {
        return (bool) $this->checked;

    }


    /**
     * Sets whether a document was uploaded to AS3
     * @param bool $as3_processed
     */
    public function setCheckedStatus(bool $checked_status) {
        $this->checked_status = (int) $checked_status;

    }

    /**
     * Returns whether a document was uploaded to AS3
     */
    public function getCheckedStatus() {
        return (bool) $this->checked_status;

    }

    /**
     * Returns whether a document already exists based on its related DA and name
     * @param int $das_id
     * @param string $name
     * @return type
     */
    public static function exists(int $das_id, string $name) {

        // We're not checking URL, because some councils add a timestamp to the URL.
        return self::findFirst([
                    "conditions" => "das_id = :das_id: AND name = :name:",
                    "bind" => [
                        "das_id" => $das_id,
                        "name" => $name
                    ]
                ]) !== false;

    }

    /**
     * Attempts to create a document and returns a status explaining what happened
     * @param int $das_id
     * @param string $name
     * @param string $url
     * @param \DateTime $date
     * @return type
     */
    public static function createIfNotExists(int $das_id, string $name, string $url, \DateTime $date = null) {

        // If document has no name
        if (strlen($name) === 0) {
            return self::DOCUMENT_NO_NAME;
        }

        // If document has no URL
        if (strlen($url) === 0) {
            return self::DOCUMENT_NO_URL;
        }

        if (self::exists($das_id, $name) === true) {
            return self::DOCUMENT_EXISTS;
        }

        $daDocument = new self();
        $daDocument->setDasId($das_id);
        $daDocument->setName($name);
        $daDocument->setUrl($url);

        // Set date if passed
        if ($date !== null && $date !== false) {
            $daDocument->setDate($date);
        }

        if ($daDocument->save()) {
            return self::DOCUMENT_SAVED;
        }
        else {
            return self::DOCUMENT_ERROR_SAVING;
        }

    }


    public static function getMetricsDocuments($from, $to, $userId, $solution){

        $das = new Das();
        $table = ($solution == 'search' ? 'das_users' : 'das_poi_users');
        $sql = "SELECT count(dd.id) as totalCount
                FROM das d, das_documents dd, $table du 
                WHERE d.id = dd.das_id
                AND d.id = du.das_id
                AND du.users_id = " . $userId . "
                AND (du.created >= '".$from."' AND du.created <= '".$to."')";


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        return $result[0]->totalCount;
    }

    public static function getMetricsIncDecDocuments($date, $userId, $solution){
        $dateNow = date('Y-m-d', strtotime($date));
        $dateBefore = date('Y-m-d', strtotime($date . '-1 days'));
        $table = ($solution == 'search' ? 'das_users' : 'das_poi_users');
        $das = new Das();
        $sql = "SELECT (
            SELECT count(dd.id)
                FROM das d, das_documents dd, $table du 
                WHERE d.id = dd.das_id
                AND d.id = du.das_id
                AND du.users_id = " . $userId . "
                AND du.created LIKE '".$dateNow."%'
            ) as totalCountToday,
            (
            SELECT count(dd.id)
                FROM das d, das_documents dd, $table du 
                WHERE d.id = dd.das_id
                AND d.id = du.das_id
                AND du.users_id = " . $userId . "
                AND du.created LIKE '".$dateBefore."%'
            ) as totalCountYesterday
            FROM das";


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        $totalCountToday = $result[0]->totalCountToday;
        $totalCountYesterday = $result[0]->totalCountYesterday;
        if($totalCountYesterday == 0){
            if($totalCountYesterday == 0 && $totalCountToday == 0){
                $return = array('status' => 'level', 'percent' => 0);
            }else{
                $return = array('status' => 'up', 'percent' => 100);
            }

        }else{
            $decrease = $totalCountYesterday - $totalCountToday;
            $percentDecrease = ($decrease / $totalCountYesterday) * 100;
            if($percentDecrease < 0){
                $return = array('status' => 'up', 'percent' => number_format($percentDecrease * -1), 0);
            }else{
                $return = array('status' => 'down', 'percent' => number_format($percentDecrease * -1, 0));
            }
        }
        return $return;
    }


    public static function getDataDocuments($from, $to, $userId, $solution){
        $table = ($solution == 'search' ? 'das_users' : 'das_poi_users');
        $das = new Das();
        $sql = "SELECT d.id, du.created 
                FROM das d, das_documents dd, $table du 
                WHERE d.id = dd.das_id
                AND d.id = du.das_id
                AND du.users_id = " . $userId . "
                AND (du.created >= '".$from."' AND du.created <= '".$to."')
                ORDER BY du.created ASC";


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        return $result;
    }

}
