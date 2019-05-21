<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 5/21/2019
 * Time: 1:24 PM
 */

namespace Aiden\Models;


class UsersShareDa extends _BaseModel
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
     * @Column(type="integer", nullable=false)
     */
    protected $users_id;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $share_number;


    /**
     * @Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $date;

    /**
     * Returns the database table name
     * @return string
     */
    public function getSource()
    {

        return 'users_share_da';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize()
    {

        $this->belongsTo('das_id', 'Aiden\Models\Das', 'id', ['alias' => 'Da']);
        $this->belongsTo('users_id', 'Aiden\Models\Users', 'id', ['alias' => 'User']);

    }


    /**
     * Returns the user id
     * @return int
     */
    public function getUserId()
    {

        return $this->users_id;

    }


    /**
     * Sets the user id
     * @return int
     */
    public function setUserId(int $users_id)
    {

        $this->users_id = $users_id;

    }

    /**
     * Returns the affected development application id
     * @return int
     */
    public function getDasId()
    {

        return $this->das_id;

    }

    /**
     * Sets the affected development application's id
     * @param int $das_id
     */
    public function setDasId(int $das_id)
    {

        $this->das_id = $das_id;

    }


    /**
     * Returns the user id
     * @return int
     */
    public function getShareNumber()
    {

        return $this->share_number;

    }


    /**
     * Sets the user id
     * @return int
     */
    public function setShareNumber(int $share_number)
    {

        $this->share_number = $share_number;

    }


    /**
     * Returns the council URL
     * @return string
     */
    public function getEmail() {
        return $this->email;

    }

    /**
     * Sets the council URL
     * @param string $council_url
     */
    public function setEmail(string $email) {
        $this->email = $email;

    }

    /**
     * Gets the lodge date
     * @return \DateTime|null
     */
    public function getDate()
    {

        if ($this->lodge_date === null) {
            return null;
        } else {

            $lodgedDateTime = \DateTime::createFromFormat('Y-m-d', $this->lodge_date);
            return $lodgedDateTime;
        }

    }

    /**
     * Sets the lodge date
     * @param string $lodge_date
     */
    public function setDate(\DateTime $date)
    {

        $this->date = $date->format('Y-m-d');

    }


    public static function recordDaMail($dasId, $usersId, $emails){
        $date = date('Y-m-d');
        // check if da is shared
        $usdCheck = self::find([
            'conditions' =>  'das_id = :dasId: AND users_id = :usersId: and date = :date: AND share_number = 1',
            'bind' => [
                'dasId' => $dasId,
                'usersId' => $usersId,
                'date' => $date
            ]
        ]);


        for($i = 0; $i < count($emails); $i++){
            $usd = new UsersShareDa();
            $usd->setDasId($dasId);
            $usd->setUserId($usersId);
            $usd->setDate(new \DateTime($date));
            $usd->setEmail($emails[$i]);
            if(count($usdCheck) > 0){
                $usd->setShareNumber(2);
            }else{
                $usd->setShareNumber(1);
            }
            $usd->save();
        }
        return true;
    }

    public static function getTotalMailCount($dasId, $usersId)
    {
        $date = date('Y-m-d');

        // get da share mail count
        $usd = self::find([
                'conditions' => 'das_id = :dasId: AND users_id = :usersId: and date = :date: AND share_number = 2',
                'bind' => [
                    'dasId' => $dasId,
                    'usersId' => $usersId,
                    'date' => $date
                ]
            ]
        );
        $daMailCount = count($usd);

        // get total count
        $usd = self::find([
                'conditions' => 'users_id = :usersId: and date = :date:',
                'bind' => [
                    'usersId' => $usersId,
                    'date' => $date
                ]
            ]
        );

        $totalMailCount = count($usd);


        return [
            'daShareCount' => $daMailCount,
            'totalMailCount' => $totalMailCount
        ];


    }

}