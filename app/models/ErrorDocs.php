<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 4/22/2019
 * Time: 5:43 AM
 */

namespace Aiden\Models;


class ErrorDocs extends _BaseModel
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
    protected $doc_id;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $as3_url;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $fixed;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $status;

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
    public function getDocId() {

        return $this->doc_id;

    }

    /**
     * Sets the user id
     * @return int
     */
    public function setDocId(int $doc_id) {

        $this->doc_id = $doc_id;

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

    /**
     * Returns the status
     * @return int
     */
    public function getStatus() {
        return $this->status;

    }

    /**
     * Sets the status
     * @param int $status
     */
    public function setStatus(int $status) {
        $this->status = $status;

    }

}