<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 4/24/2019
 * Time: 1:24 PM
 */

namespace Aiden\Models;


class DasUsersNotes extends _BaseModel
{
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
     * @Column(type="string", nullable=true)
     */
    protected $notes;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $last_updated;

    public function getSource() {

        return 'das_users_notes';

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
    public function getId() {

        return $this->id;

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
     * Returns the description
     * @return string
     */
    public function getNotes() {
        return $this->notes;

    }

    /**
     * Sets the description
     * @param string $description
     */
    public function setNotes(string $notes) {
        $this->notes = $notes;

    }


    /**
     * Gets the lodge date
     * @return \DateTime|null
     */
    public function getLastUpdated() {

        if ($this->last_updated === null) {
            return null;
        }
        else {

            $last_updated = \DateTime::createFromFormat('Y-m-d', $this->last_updated);
            return $last_updated;
        }

    }

    /**
     * Sets the lodge date
     * @param string $lodge_date
     */
    public function setLastUpdated(\DateTime $last_updated) {

        $this->last_updated = $last_updated->format('Y-m-d H:i:s');

    }

}