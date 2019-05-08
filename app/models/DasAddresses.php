<?php

namespace Aiden\Models;

use Aiden\Models\Users;
use Aiden\Models\DasUsers;

class DasAddresses extends _BaseModel {

    const ADDRESS_EXISTS = 1;
    const ADDRESS_ERROR_ON_SAVE = 2;
    const ADDRESS_CREATED = 3;
    const ADDRESS_ZERO_LENGTH = 4;

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
     * @Column(type="boolean", nullable=true)
     */
    protected $processed;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $clean_address;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $clean_processed;


    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'das_addresses';

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

    /**
     * Returns the address
     * @return string
     */
    public function getAddress() {
        return $this->address;

    }

    /**
     * Sets the address
     * @param string $address
     */
    public function setAddress(string $address) {
        $this->address = $address;

    }


    /**
     * Returns the email address
     * @return double
     */
    public function getLatitude() {
        return $this->latitude;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setLatitude(string $latitude) {
        $this->latitude = $latitude;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getLongitude() {
        return $this->longitude;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setLongitude(string $longitude) {
        $this->longitude = $longitude;
    }


    /**
     * Returns the email address
     * @return double
     */
    public function getProcessed() {
        return $this->processed;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setProcessed(bool $processed) {
        $this->processed = $processed;
    }

    /**
     * Returns the address
     * @return string
     */
    public function getCleanAddress() {
        return $this->clean_address;

    }

    /**
     * Sets the address
     * @param string $address
     */
    public function setCleanAddress(string $address) {
        $this->clean_address = $address;

    }

    /**
     * Returns the email address
     * @return double
     */
    public function getCleanProcessed() {
        return $this->clean_processed;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setCleanProcessed(bool $clean_processed) {
        $this->clean_processed = $clean_processed;
    }


    public static function exists(int $das_id, string $address) {

        return self::findFirst([
                    "conditions" => "das_id = :das_id: AND address = :address:",
                    "bind" => [
                        "das_id" => $das_id,
                        "address" => $address
                    ]
                ]) !== false;

    }

    public static function createIfNotExists(int $das_id, string $address) {

        if (strlen($address) === 0) {
            return self::ADDRESS_ZERO_LENGTH;
        }

        $daAddress = self::exists($das_id, $address);
        if ($daAddress !== false) {
            return self::ADDRESS_EXISTS;
        }
        else {

            $daAddress = new self();
            $daAddress->setDasId($das_id);
            $daAddress->setAddress($address);

            if ($daAddress->save()) {
                return self::ADDRESS_CREATED;
            }
            else {
                return self::ADDRESS_ERROR_ON_SAVE;
            }
        }

    }

}
