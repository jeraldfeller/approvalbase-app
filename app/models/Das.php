<?php

namespace Aiden\Models;

use Aiden\Models\Users;
use Aiden\Models\DasUsers;
use Aiden\Models\DasUsersSearch;

class Das extends _BaseModel {

    const STATUS_LEAD = 1;

    const STATUS_UNKNOWN = 4;

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    protected $id;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $council_id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $council_reference;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $council_reference_alt;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $council_url;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $lodge_date;

    /**
     * @Column(type="decimal", nullable=true)
     */
    protected $estimated_cost;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $status;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $created;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $crawled;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $processed;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $checked;


    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'das';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        $this->hasMany("id", "Aiden\Models\DasAddresses", "das_id", ["alias" => "Addresses"]);
        $this->hasMany("id", "Aiden\Models\DasDocuments", "das_id", ["alias" => "Documents"]);
        $this->hasMany("id", "Aiden\Models\DasParties", "das_id", ["alias" => "Parties"]);
        $this->hasMany("id", "Aiden\Models\DasPhrases", "das_id", ["alias" => "Phrases"]);
        $this->belongsTo('council_id', 'Aiden\Models\Councils', 'id', ['alias' => 'Council']);
        $this->hasManyToMany('id', 'Aiden\Models\DasUsers', 'das_id', 'users_id', 'Aiden\Models\Users', 'id', ['alias' => 'Users']);

    }

    /**
     * Returns the model's unique identifier
     * @return int
     */
    public function getId() {

        return $this->id;

    }

    /**
     * Returns the related council's unique identifier
     * @return int
     */
    public function getCouncilId() {

        return $this->council_id;

    }

    /**
     * Sets the council id
     * @param int $council_id
     */
    public function setCouncilId(int $council_id) {

        $this->council_id = $council_id;

    }

    /**
     * Returns the council reference
     */
    public function getCouncilReference() {
        return $this->council_reference;

    }

    /**
     * Sets the council reference
     * @param string $council_reference
     */
    public function setCouncilReference(string $council_reference) {

        $this->council_reference = $council_reference;

    }

    /**
     * Returns the alternative council reference
     */
    public function getCouncilReferenceAlt() {
        return $this->council_reference_alt;

    }

    /**
     * Sets the alternative council reference
     * @param string $council_reference
     */
    public function setCouncilReferenceAlt(string $council_reference_alt) {

        $this->council_reference_alt = $council_reference_alt;

    }

    /**
     * Returns the council URL
     * @return string
     */
    public function getCouncilUrl() {
        return $this->council_url;

    }

    /**
     * Sets the council URL
     * @param string $council_url
     */
    public function setCouncilUrl(string $council_url) {
        $this->council_url = $council_url;

    }

    /**
     * Returns the description
     * @return string
     */
    public function getDescription() {
        return $this->description;

    }

    /**
     * Sets the description
     * @param string $description
     */
    public function setDescription(string $description) {
        $this->description = $description;

    }

    /**
     * Gets the lodge date
     * @return \DateTime|null
     */
    public function getLodgeDate() {

        if ($this->lodge_date === null) {
            return null;
        }
        else {

            $lodgedDateTime = \DateTime::createFromFormat('Y-m-d', $this->lodge_date);
            return $lodgedDateTime;
        }

    }

    /**
     * Sets the lodge date
     * @param string $lodge_date
     */
    public function setLodgeDate(\DateTime $lodge_date) {

        $this->lodge_date = $lodge_date->format('Y-m-d H:i:s');

    }

    /**
     * Returns the estimated cost
     * @return float
     */
    public function getEstimatedCost($format_money = false) {

        if ($format_money === true) {

            setlocale(LC_MONETARY, "en_AU.UTF-8");
            $formattedNumber = number_format($this->estimated_cost);

            if ($formattedNumber !== null) {
                return $formattedNumber;
            }
        }

        return $this->estimated_cost;

    }

    /**
     * Sets the estimated cost
     * @param float $estimated_cost
     */
    public function setEstimatedCost(float $estimated_cost) {

        $this->estimated_cost = $estimated_cost;

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

    /**
     * Returns the Status String
     * @return string
     */
    public function getStatusString() {

        switch ($this->getStatus()) {

            case self::STATUS_LEAD:
                return 'lead';
            default:
                return 'unknown';
        }

    }

    /**
     * Returns a Boostrap label containing the status of this development application
     * @return type
     */
    public function getStatusLabel() {

        $statusString = ucfirst($this->getStatusString());

        switch ($this->getStatus()) {
            case self::STATUS_LEAD:
                return '<span class="label label-warning">' . $statusString . '</span>';
            default:
                return '<span class="label label-danger">' . $statusString . '</span>';
        }

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

    public function getCrawled(): bool {
        return (bool) $this->crawled;

    }

    public function setCrawled(bool $crawled) {
        $this->crawled = (int) $crawled;

    }


    public function getProcessed(): bool {
        return (bool) $this->processed;

    }

    public function setProcessed(bool $processed) {
        $this->processed = (int) $processed;

    }

    public function getChecked(): bool {
        return (bool) $this->checked;

    }

    public function setChecked(bool $checked) {
        $this->checked = (int) $checked;

    }






    /**
     * Returns a URL for the related development application
     * @param type $admin Whether URL is admin URL or not
     * @return string
     */
    public function getViewUrl($admin = false) {

        $di = \Phalcon\DI\FactoryDefault::getDefault();
        $url = $di->getUrl();

        if ($admin) {
            return $url->get('admin/leads/' . $this->getId() . '/view');
        }
        else {
            return $url->get('leads/' . $this->getId() . '/view');
        }

    }

    /**
     * Gets the related DasUsers model based on the $user argument
     * @param type $user
     * @return Aiden\Models\DevelopmentsApplicationsUsers
     */
    public function getLinkedDasUsers($user) {

        $daUsers = DasUsers::findFirst([
                    'conditions' => 'das_id = :das_id:'
                    . ' AND users_id = :users_id:',
                    'bind' => [
                        'das_id' => $this->getId(),
                        'users_id' => $user->getId(),
                    ]
        ]);

        if ($daUsers) {
            return $daUsers;
        }
        else {
            return null;
        }

    }

    /**
     * Returns a description with highlighted phrases.
     * Phrases are highlighted by encapsulating them in HTML tags
     * @param type $phrases
     * @return string
     */
    public function getHighlightedDescription($phrases = false, $fromSearch = false, $options = array(), $forEmail = false) {

        $description = $this->getDescription();

        if($fromSearch == false){
            if ($phrases === false) {
                $phrases = Phrases::find("1=1 ORDER BY CHAR_LENGTH(phrase)");
            }
            foreach ($phrases as $phrase) {
                if ($phrase->getExcludePhrase() === true) {
                    continue;
                }

                $replacePattern = $phrase->getPhrase();
                if($replacePattern != '-'){
                    // Ex: Phrase is <swimming pool>, literalSearch=true, would not match <swimming pools>
                    if ($phrase->getLiteralSearch() === true) {
                        $replacePattern = "\b" . $replacePattern . "\b";
                    }

                    // Add delimiters
                    $replacePattern = "/" . $replacePattern . "/";

                    // Add case insensitive flag
                    if ($phrase->getCaseSensitive() === false) {
                        $replacePattern .= 'i';
                    }

                    if($forEmail == false){
                        $replaceString = "<span class=\"highlighted-phrase\"><span style='opacity: 0;'>_</span>\$0<span style='opacity: 0;'>_</span></span>";
                    }else{
                        $replaceString = "<span style=\"color: #fff; background-color: #3f71fc; border-radius: 6px;\"><span style='opacity: 0;'>_</span>\$0<span style='opacity: 0;'>_</span></span>";
                    }

                    $description = preg_replace($replacePattern, $replaceString, $description);
                }
            }

        }else{
            // Add delimiters
            if($phrases != ''){

                if ($options['excludePhrase'] === 'true') {

                }

                $replacePattern = $phrases;
                if($replacePattern != '-'){
                    // Ex: Phrase is <swimming pool>, literalSearch=true, would not match <swimming pools>
                    if ($options['literalSearch'] === 'true') {
                        $replacePattern = "\b" . $replacePattern . "\b";
                    }

                    // Add delimiters
                    $replacePattern = "/" . $replacePattern . "/";

                    // Add case insensitive flag
                    if ($options['caseSensitive'] === 'false') {
                        $replacePattern .= 'i';
                    }
                    $replaceString = "<span class=\"highlighted-phrase\"><span style='opacity: 0;'>_</span>\$0<span style='opacity: 0;'>_</span></span>";
                    $description = preg_replace($replacePattern, $replaceString, $description);

//                $replacePattern = "/" . $phrases . "/";
//                $replacePattern .= '';
//                $replaceString = "<span class=\"highlighted-phrase\">\$0</span>";
//                $description = preg_replace($replacePattern, $replaceString, $description);

                }
            }

        }

        return $description;

    }

    /**
     * Generates a string of related phrases encapsulated in bootstrap labels
     * @return type
     */
    public function getRelatedPhrasesLabels() {

        $relatedPhrasesLabels = "";
        foreach ($this->Phrases as $phrase) {
            $relatedPhrasesLabels .= $phrase->getLabel() . " ";
        }

        $relatedPhrasesLabels = rtrim($relatedPhrasesLabels);

        return $relatedPhrasesLabels;

    }

    /**
     * Determines what phrases are contained within the description of this development application
     * @param type $phrases
     * @return type array()
     */
    public function getContainedPhrases($phrases = false) {

        if ($phrases === false) {
            $phrases = Phrases::find();
        }

        $detectedPhrases = [];
        foreach ($phrases as $phrase) {

            $regexPattern = $phrase->getPhrase();

            // Ex: Phrase is <swimming pool>, literalSearch=true, would not match <swimming pools>
            if ($phrase->getLiteralSearch() === true) {
                $regexPattern = "\b" . $regexPattern . "\b";
            }

            // Add delimiters
            $regexPattern = "/" . $regexPattern . "/";

            // Add case insensitive flag
            if ($phrase->getCaseSensitive() === false) {
                $regexPattern .= 'i';
            }

            if (preg_match($regexPattern, $this->getDescription()) === 1) {
                $detectedPhrases[] = $phrase;
            }
        }

        return $detectedPhrases;

    }

    /**
     * Determines what phrases are container within the description of this development application and
     * generates a HTML string based on this information.
     * @param type $phrases
     * @return type
     */
    public function getContainedPhrasesLabels($phrases = false) {

        $containedPhrasesLabels = "";
        foreach ($this->getContainedPhrases($phrases) as $phrase) {

            $containedPhrasesLabels .= $phrase->getLabel() . " ";
        }

        $containedPhrasesLabels = rtrim($containedPhrasesLabels);

        return $containedPhrasesLabels;

    }

    public static function exists($councilId, $councilReference) {

        $da = self::findFirst([
                    'conditions' => "council_reference = :council_reference: AND council_id = :council_id:",
                    'bind' => [
                        "council_reference" => $councilReference,
                        "council_id" => $councilId
                    ]
        ]);

        if ($da !== false) {
            return $da;
        }
        return false;

    }

    public static function existsByUrl($councilId, $url) {

        if (self::findFirst([
                    'conditions' => "council_url = :url: AND council_id = :council_id:",
                    'bind' => [
                        "url" => $url,
                        "council_id" => $councilId
                    ]
                ])) {
            return true;
        }
        return false;

    }

    public function afterSave() {

        $di = \Phalcon\DI::getDefault();
        $logger = $di->getLogger();

        foreach (Users::find() as $user) {

            // Check whether the user is subscribed to this council
            if ($user->isSubscribedToCouncil($this->Council) === false) {
                continue;
            }

            // The Phrases we're going to check documents for,
            // start with exclude phrases, so when we find them we can skip checking the document.
            $phrases = $user->getPhrases(["order" => "exclude_phrase DESC"]);
            foreach ($phrases as $phrase) {

                $regexPattern = $phrase->getPhrase();

                // Ex: Phrase is <swimming pool>, literalSearch=true, would not match <swimming pools>
                if ($phrase->getLiteralSearch() === true) {
                    $regexPattern = "\b" . $regexPattern . "\b";
                }

                // Add delimiters
                $regexPattern = "/" . $regexPattern . "/";

                // Add case insensitive flag
                if ($phrase->getCaseSensitive() === false) {
                    $regexPattern .= 'i';
                }

                if (preg_match($regexPattern, $this->getDescription(), $matches) === 1) {

                    // If we match a exclude phrase, stop this loop.
                    // Our user isn't interested.
                    if ($phrase->getExcludePhrase() === true) {
                        break;
                    }

                    // Create a relation between the Development Application and the phrase
                    // we do this so we can later easily display what phrases are part of a description
                    // else we'd have to re-check the description on page load.
                    $dasPhrases = DasPhrases::findFirst([
                                'conditions' => 'das_id = :das_id: AND phrases_id = :phrases_id:',
                                'bind' => [
                                    'das_id' => $this->getId(),
                                    'phrases_id' => $phrase->getId()
                                ]
                    ]);
                    if ($dasPhrases === false) {
                        $dasPhrases = new DasPhrases();
                        $dasPhrases->setDasId($this->getId());
                        $dasPhrases->setPhraseId($phrase->getId());
                        $dasPhrases->setCreated(new \DateTime());
                    }

                    if (!$dasPhrases->save()) {

                        $logger->error("Could not create relation between DA {das_id} and Phrase {phrase_id} ({error})", [
                            "das_id" => $this->getId(),
                            "phrase_id" => $phrase->getId(),
                            "error" => print_r($dasPhrases->getMessages(), true)
                        ]);
                        continue;
                    }

                    $dasUsers = DasUsers::findFirst([
                                "conditions" => "das_id = :das_id: AND users_id = :users_id:",
                                "bind" => [
                                    "das_id" => $this->getId(),
                                    "users_id" => $user->getId()
                                ]
                    ]);

                    if ($dasUsers === false) {

                        $dasUsers = new DasUsers();
                        $dasUsers->setDasId($this->getId());
                        $dasUsers->setUserId($user->getId());
                        $dasUsers->setSeen(false);
                        $dasUsers->setEmailSent(false);
                        $dasUsers->setCreated(new \DateTime());
                        $dasUsers->setStatus(DasUsers::STATUS_LEAD);
                    }

                    if (!$dasUsers->save()) {

                        $logger->error("Could not create relation between DA {das_id} and User {user_id} ({error})", [
                            "das_id" => $this->getId(),
                            "user_id" => $user->getId(),
                            "error" => print_r($dasUsers->getMessages(), true)
                        ]);
                        continue;
                    }
                }
            }
        }

    }


    /**
     * Get DA Addresses
     */

    public function getAddresses($dasId, $limitOne = false){

        if($limitOne == false){
            $addresses = DasAddresses::find([
                'conditions' => 'das_id = :dasId:',
                'bind' => [
                    'dasId' => $dasId
                ]
            ]);
            $addressArr = [];
            foreach ($addresses as $add){
                $addressArr[] = ($add->getCleanAddress() !=  NULL ? $add->getCleanAddress() : $add->getAddress());
            }
        }else{
            $addresses = DasAddresses::findFirst([
                'conditions' => 'das_id = :dasId:',
                'bind' => [
                    'dasId' => $dasId
                ]
            ]);
            $addressArr[] = $addresses->getCleanAddress();
        }





        return $addressArr;
    }


    /**
     * Get DA Documents
     */

    public static function  getDocuments($dasId){
        $documents = DasDocuments::find([
            'conditions' => 'das_id = :dasId:',
            'bind' => [
                'dasId' => $dasId
            ]
        ]);

        return $documents;
    }

    /**
     * Get DA Parties
     */

    public static function getParties($dasId){
        $parties = DasParties::find([
            'conditions' => 'das_id = :dasId:',
            'bind' => [
                'dasId' => $dasId
            ]
        ]);

        return $parties;
    }

    /**
     * Check if DA is saved
     */

    public static function getIsSaved($dasId, $usersId, $from = 'search'){
        if($from == 'search'){
            $du = DasUsersSearch::findFirst([
                'conditions' => 'das_id = :dasId: AND users_id = :usersId:',
                'bind' => [
                    'dasId' => $dasId,
                    'usersId' => $usersId
                ]
            ]);
        }else if($from == 'leads'){
            $du = DasUsers::findFirst([
                'conditions' => 'das_id = :dasId: AND users_id = :usersId:',
                'bind' => [
                    'dasId' => $dasId,
                    'usersId' => $usersId
                ]
            ]);
        }else{
            $du = DasPoiUsers::findFirst([
                'conditions' => 'das_id = :dasId: AND users_id = :usersId:',
                'bind' => [
                    'dasId' => $dasId,
                    'usersId' => $usersId
                ]
            ]);
        }


        return ($du ? $du->getStatus() : false);
    }

    // Metrics
    public static function getMetrics($from, $to, $userId){

        $das = new Das();
//        $sql = 'SELECT count(d.id) as totalCount, SUM(d.estimated_cost) as cost
//                FROM das d, das_users du
//                WHERE du.users_id = '.$userId.'
//                AND du.das_id = d.id
//                AND ((d.lodge_date >= "' . $from . '" AND d.lodge_date <= "' . $to . '") OR (d.created >= "' . $from . '" AND d.created <= "' . $to . '"))';

        $sql = 'SELECT count(d.id) as totalCount, SUM(d.estimated_cost) as cost
                FROM das d WHERE created >= "'.$from.'" AND created <= "'.$to.'"';


        $resultDas = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );


        $das = new Das();
        $sql = "SELECT (
              SELECT count(id)
                FROM das
                WHERE created LIKE '".$to."%'
            ) as totalCountToday,
            (
            SELECT count(id)
                FROM das
                WHERE created LIKE '".$from."%'
            ) as totalCountBefore
            FROM das LIMIT 1";


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );
        $totalCountToday = $result[0]->totalCountToday;
        $totalCountBefore = $result[0]->totalCountBefore;

        if($totalCountBefore == 0){
            if($totalCountBefore == 0 && $totalCountToday == 0){
                $return = array('status' => 'level', 'percent' => 0);
            }else{
                $return = array('status' => 'up', 'percent' => 100);
            }

        }else{
            $decrease = $totalCountBefore - $totalCountToday;
            $percentDecrease = ($decrease / $totalCountBefore) * 100;
            if($percentDecrease < 0){
                $return = array('status' => 'up', 'percent' => number_format($percentDecrease * -1), 0);
            }else{
                $return = array('status' => 'down', 'percent' => number_format($percentDecrease * -1, 0));
            }
        }
        return [
            'totalCount' => $resultDas[0]->totalCount,
            'totalCost' => ($resultDas[0]->cost != null ? $resultDas[0]->cost : 0),
            'incDec' => $return
        ];
    }


    public static function getDataProjects($from, $to){
        $das = new Das();
        $sql = "SELECT id, created
                FROM das
                WHERE created >= '".$from."' AND created <= '".$to."'
                ORDER BY created ASC";


        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );

        return $result;
    }


}
