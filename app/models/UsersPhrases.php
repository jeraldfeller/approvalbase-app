<?php

namespace Aiden\Models;

class UsersPhrases extends \Aiden\Models\_BaseModel {

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
    protected $phrase;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $case_sensitive;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $created;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $literal_search;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $exclude_phrase;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $metadata;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $filter_by;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $councils;
    /**
     * @Column(type="double", nullable=true)
     */
    protected $cost_from;
    /**
     * @Column(type="double", nullable=true)
     */
    protected $cost_to;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $new;


    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'users_phrases';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        $this->belongsTo("users_id", "Aiden\Models\Users", "id", ["alias" => "User"]);

    }

    /**
     * Executed after a model is created or updated.
     */
    /*
    public function afterSave() {
        $user = $this->User;

        // We check each development application
        // perhaps look at limiting the amount of DAs for optimisation

        foreach ($user->Phrases as $phrase) {
            $filterBy = ($phrase->getFilterBy() == 'all' ? 'all' : json_decode($phrase->getFilterBy()));
            $councils = json_decode($phrase->getCouncils());
            $costFrom = $phrase->getCostFrom();
            $costTo = $phrase->getCostTo();

            $councilQry = ($councils != 'all' ? " AND (council_id = " . implode(" OR council_id = ", $councils) . ")" : '');
            $includeNull = ($costFrom == 0 ? " OR estimated_cost IS NULL " : "");
            $costQry = " AND ((estimated_cost >= " . $costFrom . " AND estimated_cost <= " . $costTo . ")". $includeNull . ")";


            if($filterBy == 'all' || in_array('applicant', $filterBy) AND in_array('description', $filterBy)){

            }else if(in_array('applicant', $filterBy) AND !in_array('description', $filterBy)){

            }else if(!in_array('applicant', $filterBy) AND in_array('description', $filterBy)){
                $searchQuery = ' AND description LIKE "%'.$phrase->getPhrase().'%"';
            }



            $sql = 'SELECT * FROM das WHERE 1=1'
                    .$councilQry.$costQry.$searchQuery;
            $das = new Das();
            $das = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $das
                , $das->getReadConnection()->query($sql, [], [])
            );
            foreach ($das as $da) {

                // Loop through each of this user's phrases, because they might collide with each other
                // e.g. a user creates the phrase "demo", but has the phrase "demolition" as exclude.
                // Create a relation between the Development Application and the phrase
                // we do this so we can later easily display what phrases are part of a description
                // else we'd have to re-check the description on page load.
                if (DasPhrases::find([
                        'conditions' => 'das_id = :das_id: AND phrases_id = :phrases_id:',
                        'bind' => [
                            'das_id' => $da->getId(),
                            'phrases_id' => $phrase->getId()
                        ]
                    ])->count() > 0) {
                    continue;
                }


                $result = $this->insertData($phrase, $da, $user);
                if($result == false){
                    continue;
                }


            }
        }

    }
*/
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
     * Returns the phrase
     * @return string
     */
    public function getPhrase() {

        return $this->phrase;

    }

    /**
     * Sets the phrase
     * @param string $phrase
     */
    public function setPhrase(string $phrase) {

        $this->phrase = $phrase;

    }

    /**
     * Returns whether the phrase is case sensitive
     * @return bool
     */
    public function getCaseSensitive() {

        return (bool) $this->case_sensitive;

    }

    /**
     * Sets whether the phrase is case sensitive
     * @param bool $case_sensitive
     */
    public function setCaseSensitive(bool $case_sensitive) {

        $this->case_sensitive = $case_sensitive;

    }

    /**
     * Returns whether the phrase is case sensitive
     * @return type
     */
    public function getCaseSensitiveString() {

        return $this->getCaseSensitive() ? 'Yes' : 'No';

    }

    public function getCaseSensitiveCheckboxHtml($disabled = false) {

        $html = '<div class="checkbox">'
                . '<input id="select-case-%1$s" type="checkbox" class="dt-case-checkbox"';

        // Add checked prop
        if ($this->getCaseSensitive() === true) {
            $html .= " checked";
        }
        // Add disabled prop
        if ($disabled === true) {
            $html .= " disabled";
        }
        // Close <input>-tag
        $html .= ">";

        $html .= '<label for="select-case-%1$s"></label>';
        $html .= '</div>';

        return sprintf($html, $this->getId());

    }

    /**
     * Returns whether the phrase is case sensitive
     * @return bool
     */
    public function getMetaData() {

        return (bool) $this->metadata;

    }

    /**
     * Sets whether the phrase is case sensitive
     * @param bool $case_sensitive
     */
    public function setMetadata(bool $metadata) {

        $this->metadata = $metadata;

    }


    /**
     * Returns the phrase
     * @return string
     */
    public function getFilterBy() {

        return $this->filter_by;

    }

    /**
     * Sets the phrase
     * @param string $phrase
     */
    public function setFilterBy(string $filter_by) {

        $this->filter_by = $filter_by;

    }

    /**
     * Returns the phrase
     * @return string
     */
    public function getCouncils() {

        return $this->councils;

    }

    /**
     * Sets the phrase
     * @param string $phrase
     */
    public function setCouncils(string $councils) {

        $this->councils = $councils;

    }

    /**
     * Returns the phrase
     * @return string
     */
    public function getCostFrom() {

        return $this->cost_from;

    }

    /**
     * Sets the phrase
     * @param string $phrase
     */
    public function setCostFrom(string $cost_from) {

        $this->cost_from = $cost_from;

    }

    /**
     * Returns the phrase
     * @return string
     */
    public function getCostTo() {

        return $this->cost_to;

    }

    /**
     * Sets the phrase
     * @param string $phrase
     */
    public function setCostTo(string $cost_to) {

        $this->cost_to = $cost_to;

    }


    /**
     * Returns whether the phrase is case sensitive
     * @return bool
     */
    public function getNew() {

        return (bool) $this->new;

    }

    /**
     * Sets whether the phrase is case sensitive
     * @param bool $case_sensitive
     */
    public function setNew(bool $new) {

        $this->new = $new;

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
     * Returns how many times this phrases occurs within all development application
     * @return type
     */
    public function getOccurrences() {

        $sql = "SELECT"
                . " `das_phrases`.`das_id`,"
                . " `das_phrases`.`phrases_id`,"
                . " `das_phrases`.`created`"
                . " FROM `das_phrases`"
                . " INNER JOIN `das`"
                . " ON `das`.`id` = `das_phrases`.`das_id`"
                . " WHERE 1=1"
                . " AND `das_phrases`.`phrases_id` = :phrases_id";

        $boundParams = [
            "phrases_id" => $this->getId()
        ];

        $boundTypes = [
            "phrases_id" => \Phalcon\Db\Column::BIND_PARAM_INT
        ];

        // Base model
        $das = new Das();
        $developmentApplicationRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $das
                , $das->getReadConnection()->query($sql, $boundParams, $boundTypes)
        );

        $matches = DasPhrases::find([
                    'conditions' => 'phrases_id = :phrases_id:',
                    'bind' => [
                        'phrases_id' => $this->getId()
                    ]
        ]);

        return $matches->count();

    }

    /**
     * Returns whether this phrase should be searched for literally (i.e. not part of another word/phrase)
     * @return type
     */
    public function getLiteralSearch() {

        return (bool) $this->literal_search;

    }

    public function getLiteralSearchCheckboxHtml($disabled = false) {

        $html = '<div class="checkbox">'
                . '<input id="select-literal-%1$s" type="checkbox" class="dt-literal-checkbox"';

        // Add checked prop
        if ($this->getLiteralSearch() === true) {
            $html .= " checked";
        }
        // Add disabled prop
        if ($disabled === true) {
            $html .= " disabled";
        }
        // Close <input>-tag
        $html .= ">";

        $html .= '<label for="select-literal-%1$s"></label>';
        $html .= '</div>';

        return sprintf($html, $this->getId());

    }

    /**
     * Sets whether this phrase should be searched for literally (i.e. not part of another word/phrase)
     * @param type $literal_search
     */
    public function setLiteralSearch($literal_search) {

        $this->literal_search = $literal_search;

    }

    /**
     * Returns whether this phrase is exclude or not
     * @return type
     */
    public function getExcludePhrase() {

        return (bool) $this->exclude_phrase;

    }

    public function getExcludePhraseCheckboxHtml($disabled = false) {

        $html = '<div class="checkbox">'
                . '<input id="select-exclude-%1$s" type="checkbox" class="dt-exclude-checkbox"';

        // Add checked prop
        if ($this->getExcludePhrase() === true) {
            $html .= " checked";
        }
        // Add disabled prop
        if ($disabled === true) {
            $html .= " disabled";
        }
        // Close <input>-tag
        $html .= ">";

        $html .= '<label for="select-exclude-%1$s"></label>';
        $html .= '</div>';

        return sprintf($html, $this->getId());

    }

    /**
     * Sets whether this phrase should be exclude or not
     * @param type $exclude_phrase
     */
    public function setExcludePhrase($exclude_phrase) {

        $this->exclude_phrase = $exclude_phrase;

    }

    /**
     * Returns a bootstrap label containing this phrase
     * @return type
     */
    public function getLabel() {

        return "<span class=\"label label-default\">" . $this->getPhrase() . "</span>";

    }


    public function getApplicants($dasId){
        $applicants = '';
        $dp = DasParties::find(
            [
                'conditions' => 'das_id = :id: AND role = :role:',
                'bind' => [
                    "id" => $dasId,
                    "role" => 'Applicant'
                ]
            ]
        );
        if($dp){
            $arr = [];
            foreach($dp as $row){
                $arr[] = $row->getName();
            }
            $applicants = implode(' ', $arr);
        }

        return $applicants;

    }


    public function insertData($phrase, $da, $user){
        // If we match a exclude phrase, stop this loop.
        // Our user isn't interested.
        if ($phrase->getExcludePhrase() === true) {
            return false;
        }



        $dasPhrases = new DasPhrases();
        $dasPhrases->setDasId($da->getId());
        $dasPhrases->setPhraseId($phrase->getId());
        $dasPhrases->setCreated(new \DateTime());

        if (!$dasPhrases->save()) {
            print_r($dasPhrases->getMessages());
        }

        // Create a relation between the development application and the user
        if (DasUsers::find([
                "conditions" => "das_id = :das_id: AND users_id = :users_id:",
                "bind" => [
                    "das_id" => $da->getId(),
                    "users_id" => $user->getId()
                ]
            ])->count() > 0) {
            return false;
        }

        $dasUsers = new DasUsers();
        $dasUsers->setDasId($da->getId());
        $dasUsers->setUserId($user->getId());
        $dasUsers->setUsersPhraseId($phrase->getId());
        $dasUsers->setStatus(DasUsers::STATUS_LEAD);
        $dasUsers->setCreated(new \DateTime());
        $dasUsers->setSeen(false);
        $dasUsers->setEmailSent(false);

        if (!$dasUsers->save()) {
            // DEBUG
            die(print_r($dasUsers->getMessages()));
        }

        return true;
    }


    public static function getMetricsPhrases($from, $to, $userId){
        $up = new UsersPhrases();
        $sql = "SELECT count(id) AS totalCount
                FROM users_phrases
                WHERE users_id = ".$userId."
                AND (created >= '".$from."' AND created <= '".$to."')";

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $up
            , $up->getReadConnection()->query($sql, [], [])
        );

        return $result[0]->totalCount;
    }


}
