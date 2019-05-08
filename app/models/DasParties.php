<?php

namespace Aiden\Models;

use Aiden\Models\Users;
use Aiden\Models\DasUsers;

class DasParties extends _BaseModel {

    const PARTY_NO_NAME = 1;
    const PARTY_EXISTS = 2;
    const PARTY_SAVED = 3;
    const PARTY_ERROR_SAVING = 4;

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
     * @Column(type="string", nullable=true)
     */
    protected $role;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $name;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $phone;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $email;

    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'das_parties';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        $this->belongsTo('das_id', 'Aiden\Models\Das', 'id', ['alias' => 'Da']);

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

    public function getName() {
        return $this->name;

    }

    public function setName(string $name) {

        $this->name = $name;

    }

    public function getRole() {
        return $this->role;

    }

    public function setRole($role) {

        $this->role = $role;

    }

    public function getPhone() {
        return $this->phone;

    }

    public function setPhone(string $phone) {
        $this->phone = $phone;

    }

    public function getEmail() {
        return $this->email;

    }

    public function setEmail(string $email) {
        $this->email = $email;

    }

    public static function exists(int $das_id, $role, string $name) {

        if ($role !== null) {

            return self::findFirst([
                        "conditions" => "das_id = :das_id: AND role = :role: AND name = :name:",
                        "bind" => [
                            "das_id" => $das_id,
                            "role" => $role,
                            "name" => $name
                        ]
                    ]) !== false;
        }
        else {

            return self::findFirst([
                        "conditions" => "das_id = :das_id: AND name = :name:",
                        "bind" => [
                            "das_id" => $das_id,
                            "name" => $name
                        ]
                    ]) !== false;
        }

    }

    public static function createIfNotExists(int $das_id, $role, string $name, string $phone = null, string $email = null) {

        // If name length is zero
        if (strlen($name) === 0) {
            return self::PARTY_NO_NAME;
        }

        if (self::exists($das_id, $role, $name) === true) {
            return self::PARTY_EXISTS;
        }

        $daParty = new self();
        $daParty->setDasId($das_id);
        $daParty->setName($name);

        // Set role if passed
        if ($role !== null) {
            $daParty->setRole($role);
        }

        if ($daParty->save()) {
            return self::PARTY_SAVED;
        }
        else {
            return self::PARTY_ERROR_SAVING;
        }

    }

}
