<?php

namespace Aiden\Models;

class UsersCouncils extends \Aiden\Models\_BaseModel {

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
     * @Column(type="string", nullable=false)
     */
    protected $councils_id;

    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'users_councils';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        $this->belongsTo("users_id", "Aiden\Models\Users", "id", ["alias" => "User"]);
        $this->belongsTo("councils_id", "Aiden\Models\Councils", "id", ["alias" => "Council"]);

    }

    /**
     * Returns the model's unique identifier
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
     * Returns the council id
     * @return int
     */
    public function getCouncilId() {

        return $this->councils_id;

    }

    /**
     * Sets the council id
     * @return int
     */
    public function setCouncilId(int $councils_id) {

        $this->councils_id = $councils_id;

    }

}
