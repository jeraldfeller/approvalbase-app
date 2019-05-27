<?php

namespace Aiden\Models;

class Councils extends _BaseModel
{

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    protected $id;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $name;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $website_url;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $logo_url;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $last_scrape;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $command;

    /**
     * Returns the database table name
     * @return string
     */
    public function getSource()
    {

        return 'councils';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize()
    {

        // Relation to Das
        $this->hasMany('id', 'Aiden\Models\Das', 'council_id', ['alias' => 'Das']);

        // Relation to Users
        $this->hasManyToMany('id', 'Aiden\Models\UsersCouncils', 'councils_id', 'users_id', 'Aiden\Models\Users', 'id', ['alias' => 'Users']);

    }

    /**
     * Returns the unique identifier
     * @return int
     */
    public function getId()
    {
        return $this->id;

    }

    /**
     * Returns the council name
     * @return string
     */
    public function getName()
    {
        return $this->name;

    }

    /**
     * Sets the council name
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;

    }

    /**
     * Returns the council website URL
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->website_url;

    }

    /**
     * Sets the council website URL
     * @param string $website
     */
    public function setWebsiteUrl(string $website_url)
    {
        $this->website_url = $website_url;

    }

    /**
     * Gets the command to run the scraper with
     * @return string|null
     */
    public function getCommand()
    {
        return $this->command;

    }

    /**
     * Sets the command to run the scraper with
     */
    public function setCommand(string $command)
    {
        $this->command = $command;

    }

    /**
     * Returns the council's logo URL
     * @return type
     */
    public function getLogoUrl()
    {
        return $this->logo_url;

    }

    /**
     * Sets the logo URL
     * @param string $logo_url
     */
    public function setLogoUrl(string $logo_url)
    {
        $this->logo_url = $logo_url;

    }

    /**
     * Gets the last scrape datetime
     * @return \DateTime|null
     */
    public function getLastScrape()
    {

        $lastScrapeDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->last_scrape);

        if ($lastScrapeDateTime !== false || $lastScrapeDateTime !== null) {
            return $lastScrapeDateTime;
        } else {
            return false;
        }

    }

    /**
     * Sets the last scrape datetime
     * @param string $created
     */
    public function setLastScrape($last_scrape)
    {

        if ($last_scrape !== null) {
            $this->last_scrape = $last_scrape->format('Y-m-d H:i:s');
        } else {
            $this->last_scrape = null;
        }

    }

    /**
     * Gets the last scrape datetime as a string
     */
    public function getLastScrapeString($timeago = true)
    {

        $lastScrapeDateTime = $this->getLastScrape();

        if ($lastScrapeDateTime !== false && $lastScrapeDateTime !== null) {

            if ($timeago === true) {

                $timeago = '<time class="timeago" datetime="%s">%s</time>';
                return sprintf($timeago, $lastScrapeDateTime->format("c"), $lastScrapeDateTime->format('d/m/Y'));
            } else {

                return $lastScrapeDateTime->format("d/m/Y");
            }
        } else {
            return "";
        }

    }

    public function getLastLeadString()
    {

        $lastLeadDateTime = $this->getLastLeadDateTime();

        if ($lastLeadDateTime !== false && $lastLeadDateTime !== null) {

            $timeago = '<time class="timeago" datetime="%s">%s</time>';
            return sprintf($timeago, $lastLeadDateTime->format('c'), $lastLeadDateTime->format('d/m/Y'));
        } else {

            return "";
        }

    }

    public function getLastLeadDateTime()
    {

        $sql = "SELECT `das`.*"
            . " FROM `das`"
            . " WHERE 1=1"
            . " AND `das`.`council_id` = :council_id"
            . " ORDER BY `das`.`created`"
            . " DESC LIMIT 1";

        $boundParams["council_id"] = $this->getId();
        $boundTypes["council_id"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Rows to be displayed
        $das = new Das();
        $dasRows = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, $boundParams, $boundTypes)
        );

        $da = $dasRows->getFirst();

        if ($da === false || $da === null) {
            return false;
        }

        if ($da->getCreated() === false || $da->getCreated() === null) {
            return false;
        }

        return $da->getCreated();

    }

    public function getUncrawledDas()
    {
        // get parent council for merged councils
        switch ($this->getId()) {
            case 33: // Marrickville -> parent Innerwest
                $id = 17;
                break;
            case 1: // Ashfield -> parent Innerwest
                $id = 17;
                break;
            case 16: // Leichhardt -> parent Innerwest
                $id = 17;
                break;
            default:
                $id = $this->getId();
        }
        return Das::find([
            "conditions" => "council_id = :council_id: AND DATEDIFF(NOW(), created) < 14 ",
            "bind" => [
                "council_id" => $id
            ]
        ]);

    }


    // Metrics
    public static function getMetrics($from, $to, $userId)
    {

        $das = new Das();
//        $sql = 'SELECT count(c.id) as totalCount
//                FROM councils c, das d, das_users du
//                WHERE  d.council_id = c.id
//                AND d.id = du.das_id
//                AND du.users_id = '.$userId.'
//                AND ((d.lodge_date >= "' . $from . '" AND d.lodge_date <= "' . $to . '") OR (d.created >= "' . $from . '" AND d.created <= "' . $to . '"))
//                GROUP BY c.name';

        $sql = 'SELECT count(c.id) as totalCount
                FROM councils c';

        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );
        return $result[0]->totalCount;
    }


    public static function subscribeToAll($userId)
    {
        $uc = new Councils();
        $sql = 'SELECT id
                FROM councils';
        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $uc
            , $uc->getReadConnection()->query($sql, [], [])
        );

        foreach ($result as $row) {
            $councilId = $row->id;
            $usersCouncils = new UsersCouncils();
            $usersCouncils->setUserId($userId);
            $usersCouncils->setCouncilId($councilId);
            $usersCouncils->save();
        }

        $uc = null;
        $usersCouncils = null;


        return true;

    }

    public static function getCouncilById($id)
    {
        return self::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);
    }
}
