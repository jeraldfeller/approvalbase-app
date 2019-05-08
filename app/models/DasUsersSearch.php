<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 11/13/2018
 * Time: 7:25 AM
 */

namespace Aiden\Models;


class DasUsersSearch extends _BaseModel
{
    const STATUS_UNKNOWN = -1;

    const STATUS_LEAD = 1;

    const STATUS_SAVED = 2;

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    protected $das_id;

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    protected $users_id;

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
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'das_users_search';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        $this->belongsTo('das_id', 'Aiden\Models\Das', 'id', ['alias' => 'Da']);
        $this->belongsTo('users_id', 'Aiden\Models\Users', 'id', ['alias' => 'User']);

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


    public static function getSavedSearch($userId, $leadId){
        $dasUserSearch = new DasUsersSearch();
        $sqlParams = [];
        $sqlTypes = [];
        $sql = 'SELECT status, created, seen, email_sent FROM das_users_search WHERE das_id = '.$leadId.' AND users_id = '.$userId;
        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $dasUserSearch
            , $dasUserSearch->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );

        return $result;
    }

    public static function updateSavedSearch($userId, $leadId, $status){
        $dasUserSearch = self::findFirst([
            'conditions' => 'das_id = :das_id: AND users_id = :users_id:',
            'bind' => [
                "das_id" => $leadId,
                "users_id" => $userId
            ]
        ]);
        try{

            if($dasUserSearch){
                // it exists, update
                $dasUserSearch->setDasId($leadId);
                $dasUserSearch->setUserId($userId);
                $dasUserSearch->setStatus($status);
                $dasUserSearch->setSeen(0);
                if ($dasUserSearch->save()) {
                    return true;
                }else{
                    return false;
                }
            }else{
                $dasUserSearch = new DasUsersSearch();
                $dasUserSearch->setDasId($leadId);
                $dasUserSearch->setUserId($userId);
                $dasUserSearch->setStatus($status);
                $dasUserSearch->setSeen(0);
                $dasUserSearch->setEmailSent(0);
                $dasUserSearch->setCreated(new \DateTime());
                if ($dasUserSearch->save()) {
                    return true;
                }else{
                    return false;
                }
            }
        }catch (\ErrorException $e){
            return false;
        }

    }
}