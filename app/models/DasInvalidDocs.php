<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 4/4/2019
 * Time: 4:52 AM
 */

namespace Aiden\Models;


class DasInvalidDocs extends _BaseModel
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
     * @Column(type="integer", nullable=true)
     */
    protected $fixed;



    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        $this->belongsTo('das_id', 'Aiden\Models\Das', 'id', ['alias' => 'Das']);

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
    public function getDasId() {

        return $this->das_id;

    }

    /**
     * Sets the user id
     * @return int
     */
    public function setDasId(int $das_id) {

        $this->das_id = $das_id;

    }

    /**
     * Returns the status
     * @return int
     */
    public function getFixed() {
        return $this->fixed;

    }

    /**
     * Sets the status
     * @param int $status
     */
    public function setFixed(int $fixed) {
        $this->fixed = $fixed;

    }
}