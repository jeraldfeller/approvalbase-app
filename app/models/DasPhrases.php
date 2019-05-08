<?php

namespace Aiden\Models;

class DasPhrases extends _BaseModel {

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
    protected $phrases_id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $created;

    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'das_phrases';

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
     * Returns the phrase id
     * @return type
     */
    public function getPhraseId() {

        return $this->phrases_id;

    }

    /**
     * Sets the phrase id
     * @return type
     */
    public function setPhraseId(int $phrases_id) {

        $this->phrases_id = $phrases_id;

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



}
