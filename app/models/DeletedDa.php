<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 4/23/2019
 * Time: 1:28 PM
 */

namespace Aiden\Models;


class DeletedDa extends _BaseModel
{
    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    protected $id;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $council_reference;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $council_url;

    /**
     * Returns the model's unique identifier
     * @return int
     */
    public function getId() {

        return $this->id;

    }

    /**
     * Gets the document's AS3 URL
     * @return type
     */
    public function getCouncilReference() {
        return $this->council_reference;

    }

    /**
     * Sets the document's AS3 URL
     * @param string $url
     */
    public function setCouncilReference(string $council_reference) {

        $this->council_reference = $council_reference;

    }

    /**
     * Gets the document's AS3 URL
     * @return type
     */
    public function getCouncilUrl() {
        return $this->council_url;

    }

    /**
     * Sets the document's AS3 URL
     * @param string $url
     */
    public function setCouncilUrl(string $council_url) {

        $this->council_url = $council_url;

    }

}