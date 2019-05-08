<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 4/3/2019
 * Time: 9:34 AM
 */

namespace Aiden\Models;


class UsersEmail extends _BaseModel
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
    protected $users_id;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $date_created;



    /**
     * Sets the database relations within the app
     */
    public function initialize() {

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
     * Returns the phrase
     * @return string
     */
    public function getEmail() {

        return $this->email;

    }

    /**
     * Sets the phrase
     * @param string $phrase
     */
    public function setEmail(string $email) {

        $this->email = $email;

    }

    /**
     * Returns the phrase
     * @return string
     */
    public function getType() {

        return $this->type;

    }

    /**
     * Sets the phrase
     * @param string $phrase
     */
    public function setType(string $type) {

        $this->type = $type;

    }

    /**
     * Gets the creation date
     * @return \DateTime|null
     */
    public function getDateCreated() {

        $createdDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->date_created);
        return $createdDateTime;

    }

    /**
     * Sets the creation date
     * @param string $created
     */
    public function setDateCreated(\DateTime $date_created) {

        $this->date_created = $date_created->format('Y-m-d H:i:s');

    }

}