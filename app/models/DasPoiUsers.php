<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 12/13/2018
 * Time: 7:06 AM
 */

namespace Aiden\Models;


class DasPoiUsers extends _BaseModel {

    const STATUS_UNKNOWN = -1;

    const STATUS_LEAD = 1;

    const STATUS_SAVED = 2;

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
     * @Column(type="integer", nullable=false)
     */
    protected $users_id;

    /**
     * @Column(type="integer", nullable=true)
     */

    protected $users_poi_id;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $status;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $created;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $seen;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $email_sent;

    /**
     * @Column(type="decimal", nullable=false)
     */
    protected $distance;

    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'das_poi_users';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        $this->belongsTo('das_id', 'Aiden\Models\Das', 'id', ['alias' => 'Da']);
        $this->belongsTo('users_id', 'Aiden\Models\Users', 'id', ['alias' => 'User']);
        $this->belongsTo('users_poi_id', 'Aiden\Models\Poi', 'id', ['alias' => 'Poi']);

    }

    /**
     * Returns the user id
     * @return int
     */
    public function getId() {

        return $this->id;

    }

    /**
     * Sets the user id
     * @return int
     */
    public function setId(int $id) {

        $this->id = $id;

    }

    /**
     * Returns the user id
     * @return int
     */
    public function getUserId() {

        return $this->users_id;

    }


    /**
     * Sets the user id
     * @return int
     */
    public function setUserId(int $users_id) {

        $this->users_id = $users_id;

    }

    /**
     * Returns the user id
     * @return int
     */
    public function getUsersPoiId() {

        return $this->users_poi_id;

    }

    /**
     * Sets the user id
     * @return int
     */
    public function setUsersPoiId(int $users_poi_id) {

        $this->users_poi_id = $users_poi_id;

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
     * Returns the status
     * @return int
     */
    public function getStatus() {
        return (int) $this->status;

    }

    /**
     * Sets the status
     * @param int $status
     */
    public function setStatus(int $status) {
        $this->status = $status;

    }

    /**
     * Returns the Status String
     * @return string
     */
    public function getStatusString() {

        switch ($this->getStatus()) {

            case self::STATUS_LEAD:
                return "lead";
            case self::STATUS_SAVED:
                return "saved";
            default:
                return "Unknown";
        }

    }

    /**
     * Returns the status as a bootstrap label
     * @return type
     */
    public function getStatusLabel() {

        $statusString = ucfirst($this->getStatusString());

        switch ($this->getStatus()) {
            case self::STATUS_LEAD:
                return '<span class="label label-default">' . $statusString . '</span>';
            case self::STATUS_SAVED:
                return '<span class="label label-success">' . $statusString . '</span>';
            default:
                return '<span class="label label-warning">' . $statusString . '</span>';
        }

    }

    /**
     * Gets the creation date
     * @return \DateTime|null
     */
    public function getCreated() {

        $createdDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->created);
        return $createdDateTime;

    }

    /**
     * Sets the creation date
     * @param string $created
     */
    public function setCreated(\DateTime $created) {

        $this->created = $created->format('Y-m-d H:i:s');

    }

    /**
     * Returns whether a user has seen the related development application
     * @return bool
     */
    public function getSeen() {

        return (bool) $this->seen;

    }

    /**
     * Sets whether a user has seen the related development application
     * @param type $seen
     */
    public function setSeen(bool $seen) {
        $this->seen = (int)$seen;

    }

    /**
     * Returns whether a user has been emailed after relation was made between user and DA
     * @return bool
     */
    public function getEmailSent() {

        return (bool) $this->email_sent;

    }

    /**
     * Sets whether a user has been emailed after relation was made between user and DA
     * @param type $emailSent
     */
    public function setEmailSent(bool $emailSent) {
        $this->email_sent = (int) $emailSent;

    }


    /**
     * Returns the description
     * @return string
     */
    public function getDistance() {
        return $this->distance;

    }

    /**
     * Sets the description
     * @param string $description
     */
    public function setDistance(float $distance) {
        $this->distance = $distance;

    }

    /**
     * Determines whether a $status is a valid status
     * @param type $status
     * @return boolean
     */
    public static function isValidStatus($status) {

        switch ($status) {
            case self::STATUS_LEAD: return true;
            case self::STATUS_SAVED: return true;
            default: return false;
        }

    }

    public static function getMetricsDasPoiUsers($from, $to, $userId){
        $dpu = new DasPoiUsers();
        $sql = "SELECT COUNT(das_id) AS totalCount
                FROM das_poi_users
                WHERE users_id = ".$userId."
                AND (created >= '".$from."' AND created <= '".$to."')";

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $dpu
            , $dpu->getReadConnection()->query($sql, [], [])
        );

        return $result[0]->totalCount;
    }


    public static function getMetricsDasUsers($from, $to, $userId){
        $du = new DasUsers();
        $sql = "SELECT COUNT(das_id) AS totalCount
                FROM das_users
                WHERE users_id = ".$userId."
                AND (created >= '".$from."' AND created <= '".$to."')";

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $du
            , $du->getReadConnection()->query($sql, [], [])
        );

        return $result[0]->totalCount;
    }


    public static function getMetricsIncDecAlerts($date, $userId){
        $dateNow = date('Y-m-d', strtotime($date));
        $dateBefore = date('Y-m-d', strtotime($date . '-1 days'));
        $dpu = new DasPoiUsers();
        $sql = "SELECT (
            SELECT count(das_id)
                FROM das_poi_users
                WHERE users_id = " . $userId . "
                AND created LIKE '".$dateNow."%'
            ) as totalCountToday,
            (
            SELECT count(das_id)
                FROM das_poi_users
                WHERE users_id = " . $userId . "
                AND created LIKE '".$dateBefore."%'
            ) as totalCountYesterday
            FROM das_poi_users";


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $dpu
            , $dpu->getReadConnection()->query($sql, [], [])
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


    public static function getDataAlerts($from, $to, $userId){
        $das = new Das();
        $sql = "SELECT das_id, created
                FROM das_poi_users
                WHERE users_id = ".$userId."
                AND (created >= '".$from."' AND created <= '".$to."') 
                ORDER BY created ASC";


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        return $result;
    }


    public static function getDataSavedAlerts($from, $to, $userId){
        $das = new Das();
        $sql = "SELECT das_id, created
                FROM das_poi_users
                WHERE users_id = ".$userId."
                AND status = ".DasUsers::STATUS_SAVED."
                AND (created >= '".$from."' AND created <= '".$to."') 
                ORDER BY created ASC";


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        return $result;
    }

    public static function getTopSources($from, $to, $userId){
        $das = new Das();
        $sql = "SELECT c.name
                FROM councils c, das d, das_poi_users du
                WHERE d.id = du.das_id
                AND d.council_id = c.id
                AND du.users_id = ".$userId."
                AND (du.created >= '".$from."' AND du.created <= '".$to."')";

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        return $result;
    }

}