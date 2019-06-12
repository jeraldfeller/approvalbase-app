<?php

namespace Aiden\Models;

class Users extends _BaseModel {

    const LEVEL_REGISTERED = 0;

    const LEVEL_USER = 1;

    const LEVEL_ADMINISTRATOR = 255;

    /**
     * @Identity
     * @Primary
     * @Column(type="integer", nullable=false) 
     */
    protected $id;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $restore_id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $last_name;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $mobile_number;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $website_url;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $company_name;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $company_city;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $company_country;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @Column(type="string", length=128, nullable=false)
     */
    protected $password_hash;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $created;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $last_login;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $level;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $image_url;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $seen_modal;


    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $onboarding_alerts;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $onboarding_filter;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $phrase_detect_email;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $subscription_status;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $stripe_customer_id;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $send_notifications_on_leads;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $show_alerts;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $solution;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $verification_code;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $verified;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $clicked_da;


    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'users';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {

        // Relation to DasUsers
        $this->belongsTo('id', 'Aiden\Models\DasUsers', 'users_id', ['alias' => 'DasUsers']);

        // Relation to Phrases
        $this->hasMany('id', 'Aiden\Models\UsersPhrases', 'users_id', ["alias" => "Phrases"]);

        // Relation to Das
        $this->hasManyToMany('id', 'Aiden\Models\DasUsers', 'users_id', 'das_id', 'Aiden\Models\Das', 'id', ['alias' => 'Das']);

        // Relation to Councils
        $this->hasManyToMany('id', 'Aiden\Models\UsersCouncils', 'users_id', 'councils_id', 'Aiden\Models\Councils', 'id', ['alias' => 'Councils']);

    }

    /**
     * Returns the model's unique identifier
     * @return int
     */
    public function getId() {

        return $this->id;

    }


    /**
     * Sets the email address
     * @param string $email
     */
    public function setRestoreId(string $restore_id) {

        $this->restore_id = $restore_id;

    }

    /**
     * Returns the email address
     * @return string
     */
    public function getRestoreId() {

        return $this->restore_id;

    }

    /**
     * Returns the email address
     * @return string
     */
    public function getName() {

        return $this->name;

    }


    /**
     * Returns the email address
     * @return string
     */
    public function getLastName() {

        return $this->last_name;

    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setName(string $name) {

        $this->name = $name;

    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setLastName(string $last_name) {

        $this->last_name = $last_name;

    }


    /**
     * Returns the email address
     * @return string
     */
    public function getMobileNumber() {

        return $this->mobile_number;

    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setMobileNumber(string $mobile_number) {

        $this->mobile_number = $mobile_number;

    }



    /**
     * Returns the email address
     * @return string
     */
    public function getWebsiteUrl() {

        return $this->website_url;

    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setWebsiteUrl(string $website_url) {

        $this->website_url = $website_url;

    }

    /**
     * Returns the email address
     * @return string
     */
    public function getCompanyName() {

        return $this->company_name;

    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setCompanyName(string $company_name) {

        $this->company_name = $company_name;

    }

    /**
     * Returns the email address
     * @return string
     */
    public function getCompanyCity() {

        return $this->company_city;

    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setCompanyCity(string $company_city) {

        $this->company_city = $company_city;

    }

    /**
     * Returns the email address
     * @return string
     */
    public function getCompanyCountry() {

        return $this->company_country;

    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setCompanyCountry(string $company_country) {

        $this->company_country = $company_country;

    }

    /**
     * Returns the email address
     * @return string
     */
    public function getEmail() {

        return $this->email;

    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setEmail(string $email) {

        $this->email = $email;

    }

    /**
     * Returns the password hash
     * @return string
     */
    public function getPasswordHash() {
        return $this->password_hash;

    }

    /**
     * Returns the password_hash
     * @param string $password_hash
     */
    public function setPasswordHash(string $password_hash) {

        $this->password_hash = $password_hash;

    }

    /**
     * Returns the user's level
     * @return int
     */
    public function getLevel(): int {
        return $this->level;

    }

    /**
     * Sets the user's level
     * @param int $level
     */
    public function setLevel(int $level) {

        $this->level = $level;

    }

    /**
     * Returns the user's level string e.g. Administrator
     * @return string
     */
    public function getLevelString() {

        switch ($this->getLevel()) {

            case self::LEVEL_REGISTERED:
                return 'Registered';
            case self::LEVEL_USER:
                return 'User';
            case self::LEVEL_ADMINISTRATOR:
                return 'Administrator';
            default:
                return 'Unknown';
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

    /**
     * Gets the creation date
     * @return \DateTime|null
     */
    public function getLastLogin() {

        $lastLoginDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->last_login);
        return $lastLoginDateTime;

    }

    /**
     * Sets the creation date
     * @param string $created
     */
    public function setLastLogin(\DateTime $last_login) {

        $this->last_login = $last_login->format('Y-m-d H:i:s');

    }

    /**
     * Gets the user's profile image url
     * @return string
     */
    public function getImageUrl() {
        return $this->image_url;

    }

    /**
     * Sets the user's profile image URL
     * @param type $url
     */
    public function setImageUrl($url) {

        $this->image_url = $url;

    }

    /**
     * Returns whether a user wants to be emailed after a phrase is detected in a DA
     * @return bool
     */
    public function getPhraseDetectEmail() {

        return (bool) $this->phrase_detect_email;

    }

    /**
     * Sets whether a user wants to be emailed after a phrase is detected in a DA
     * @param type $phraseDetectEmail
     */
    public function setPhraseDetectEmail(bool $phraseDetectEmail) {
        $this->phrase_detect_email = (int) $phraseDetectEmail;

    }

    /**
     * Returns whether a user wants to be emailed after a phrase is detected in a DA
     * @return bool
     */
    public function getSeenModal() {

        return (bool) $this->seen_modal;

    }

    /**
     * Sets whether a user wants to be emailed after a phrase is detected in a DA
     * @param type $phraseDetectEmail
     */
    public function setSeenModal(bool $seenModal) {
        $this->seen_modal = (int) $seenModal;

    }



    /**
     * Returns whether a user wants to be emailed after a phrase is detected in a DA
     * @return bool
     */
    public function getOnboardingAlerts() {

        return (bool) $this->onboarding_alerts;

    }

    /**
     * Sets whether a user wants to be emailed after a phrase is detected in a DA
     * @param type $phraseDetectEmail
     */
    public function setOnboardingAlerts(bool $onboarding_alerts) {
        $this->onboarding_alerts = (int) $onboarding_alerts;

    }

    /**
     * Returns whether a user wants to be emailed after a phrase is detected in a DA
     * @return bool
     */
    public function getOnboardingFilter() {

        return (bool) $this->onboarding_filter;

    }

    /**
     * Sets whether a user wants to be emailed after a phrase is detected in a DA
     * @param type $phraseDetectEmail
     */
    public function setOnboardingFilter(bool $onboarding_filter) {
        $this->onboarding_filter = (int) $onboarding_filter;

    }


    /**
     * Gets the user's subscription status
     * @return string
     */
    public function getSubscriptionStatus() {
        return $this->subscription_status;

    }

    /**
     * Sets the user's profile image URL
     * @return string
     */
    public function setSubscriptionStatus($subscription_status) {

        $this->subscription_status = $subscription_status;

    }

    /**
     * Gets the user's subscription status
     * @return string
     */
    public function getStripeCustomerId() {
        return $this->stripe_customer_id;

    }

    /**
     * Sets the user's profile image URL
     * @return string
     */
    public function setStripeCustomerId($stripe_customer_id) {

        $this->stripe_customer_id = $stripe_customer_id;

    }


    /**
     * Returns whether a user wants to be emailed after a phrase is detected in a DA
     * @return bool
     */
    public function getSendNotificationsOnLeads() {

        return (bool) $this->send_notifications_on_leads;

    }

    /**
     * Sets whether a user wants to be emailed after a phrase is detected in a DA
     * @param type $phraseDetectEmail
     */
    public function setSendNotificationsOnLeads(bool $send_notifications_on_leads) {
        $this->send_notifications_on_leads = (int) $send_notifications_on_leads;

    }


    /**
     * Returns whether a user wants to be emailed after a phrase is detected in a DA
     * @return bool
     */
    public function getShowAlerts() {

        return (bool) $this->show_alerts;

    }

    /**
     * Sets whether a user wants to be emailed after a phrase is detected in a DA
     * @param type $phraseDetectEmail
     */
    public function setShowAlerts(bool $show_alerts) {
        $this->show_alerts = (int) $show_alerts;

    }


    /**
     * Gets the user's subscription status
     * @return string
     */
    public function getSolution() {
        return $this->solution;

    }

    /**
     * Sets the user's profile image URL
     * @return string
     */
    public function setSolution($solution) {

        $this->solution = $solution;

    }


    /**
     * Gets the user's subscription status
     * @return string
     */
    public function getVerificationCode() {
        return $this->verification_code;

    }

    /**
     * Sets the user's profile image URL
     * @return string
     */
    public function setVerificationCode($verication_code) {

        $this->verification_code = $verication_code;

    }


    /**
     * Returns whether a user wants to be emailed after a phrase is detected in a DA
     * @return bool
     */
    public function getVerified() {

        return (bool) $this->verified;

    }

    /**
     * Sets whether a user wants to be emailed after a phrase is detected in a DA
     * @param type $phraseDetectEmail
     */
    public function setVerified(bool $verified) {
        $this->verified = (int) $verified;

    }

    /**
     * Gets the user's subscription status
     * @return string
     */
    public function getClickedDa() {
        return $this->clicked_da;

    }

    /**
     * Sets the user's profile image URL
     * @return string
     */
    public function setClickedDa($clicked_da) {

        $this->clicked_da = $clicked_da;

    }


    /**
     * Returns whether a user is subscribed to a Phrase
     * @param type $phrase_id
     * @return bool
     */
    public function isSubscribedToPhraseId(int $phrase_id) {

        return UsersPhrases::findFirst([
                    'conditions' => 'users_id = :users_id: AND phrases_id = :phrases_id:',
                    'bind' => [
                        'users_id' => $this->getId(),
                        'phrases_id' => $phrase_id
                    ]
                ]) !== false;

    }

    /**
     * Returns whether the user is subscribed to a specific council
     * @param \Aiden\Models\Aiden\Models\Councils $council
     * @return bool
     */
    public function isSubscribedToCouncil(\Aiden\Models\Councils $council) {

        return UsersCouncils::findFirst([
                    "conditions" => "users_id = :users_id: AND councils_id = :councils_id:",
                    "bind" => [
                        "users_id" => $this->getId(),
                        "councils_id" => $council->getId()
                    ]
                ]) !== false;

    }

    public function getSubscribedToCouncilText(\Aiden\Models\Councils $council) {

        $di = \Phalcon\DI\FactoryDefault::getDefault();
        $url = $di->getUrl();

        $subscribed = $this->isSubscribedToCouncil($council);

        $html = ''
                . sprintf('<a id="subscribe_council_%s" href="%s" class="text-%s">'
                        , $council->getId()
                        , $url->get("councils/" . $council->getId() . '/' . ($subscribed ? "unsubscribe" : "subscribe"))
                        , $subscribed ? "success" : "danger")
                . ($subscribed ? "Subscribed" : "Not subscribed")
                . '</a>'
                . '</div>';

        return $html;

    }

    /**
     * Returns all development applications by DasUsers::status (e.g. Saved, Lead)
     * @param int $status
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    public function getDasByStatus($statuses) {

        $boundParams = [];
        $boundTypes = [];

        $sql = $this->getDasByStatusQuery($statuses, $boundParams, $boundTypes);

        $das = new Das();
        $developmentApplicationRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $das
                , $das
                        ->getReadConnection()
                        ->query($sql, $boundParams, $boundTypes)
        );

        return $developmentApplicationRows;

    }

    public function getDasByStatusQuery($statuses, &$boundParams, &$boundTypes) {

        $sql = "SELECT"
                . " `das`.`id`,"
                . " `das`.`council_id`,"
                . " `das`.`council_reference`,"
                . " `das`.`council_reference_alt`,"
                . " `das`.`council_url`,"
                . " `das`.`description`,"
                . " `das`.`lodge_date`,"
                . " `das`.`estimated_cost`,"
                . " `das`.`status`,"
                . " `das`.`created`"
                . " FROM `das`"
                . " INNER JOIN `das_users`"
                . " ON `das_users`.`das_id` = `das`.`id`"
                . " WHERE 1=1"
                . " AND `das_users`.`users_id` = :users_id";

        $boundParams["users_id"] = $this->getId();
        $boundTypes["users_id"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // If multiple statuses (e.g. both Saved and Leads)
        if (is_array($statuses)) {

            $sql .= " AND ";

            // Add brackets if more than a single status
            if (count($statuses) > 1) {
                $sql .= "(";
            }

            foreach ($statuses as $i => $status) {

                $sql .= "`das_users`.`status` = :user_status" . $i;

                // Keep adding OR unless last status
                if ($i < (count($statuses) - 1)) {
                    $sql .= " OR ";
                }

                $boundParams["user_status" . $i] = $status;
                $boundTypes["user_status" . $i] = \Phalcon\Db\Column::BIND_PARAM_INT;
            }

            // Add brackets if more than a single status
            if (count($statuses) > 1) {
                $sql .= ")";
            }
        }

        // Status is a single integer
        else {

            $sql .= " AND `das_users`.`status` = :user_status";

            $boundParams["user_status"] = $statuses;
            $boundTypes["user_status"] = \Phalcon\Db\Column::BIND_PARAM_INT;
        }

        // Only show DAs with documents
        $sql .= " AND EXISTS (SELECT 1"
                . " FROM `das_documents` docs"
                . " WHERE docs.`das_id` = `das`.`id`)";

        return $sql;

    }

    /**
     * Returns whether a user is an administrator
     * @return boolean
     */
    public function isAdministrator() {

        return ($this->getLevel() === self::LEVEL_ADMINISTRATOR);

    }


    public static function updateUserInfo($data, $userId){

        $user = self::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                "id" => $userId
            ]
        ]);

        if($user){
            $user->setName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setWebsiteUrl($data['websiteUrl']);
            $user->setCompanyName($data['companyName']);
            $user->setCompanyCity($data['companyCity']);
            $user->setCompanyCountry($data['companyCountry']);
            if($data['avatar'] != ''){
                $user->setImageUrl($data['avatar']);
            }
            if ($user->save()) {
                return array(
                   'status' => true,
                    'avatar' => $data['avatarBasePath'],
                    'name' => $data['firstName'] . ' ' . $data['lastName']
                );
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    public static function getUsersInfoById($id){
        $user = self::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                "id" => $id
            ]
        ]);

        if($user){
            return array(
                'id' => $user->getId(),
                'restoreId' => $user->getRestoreId(),
                'firstName' => $user->getName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'websiteUrl' => $user->getWebsiteUrl(),
                'companyName' => $user->getCompanyName(),
                'companyCity' => $user->getCompanyCity(),
                'companyCountry' => $user->getCompanyCountry(),
                'level' => ($user->getLevel() == 255 ? 'Administrator' : 'User'),
                'imageUrl' => $user->getImageUrl(),
                'subscriptionStatus' => $user->getSubscriptionStatus(),
                'seenModal' => $user->getSeenModal(),
                'onboardingAlerts' => $user->getOnboardingAlerts(),
                'onboardingFilter' => $user->getOnboardingFilter(),
                'sendNotificationsOnLeads' => $user->getSendNotificationsOnLeads(),
                'showAlerts' => $user->getShowAlerts(),
                'solution' => $user->getSolution()
            );
        }else{
            return false;
        }
    }


    public static function checkUserSubscriptionStatus($userId){

        $dateNow = date('Y-m-d');
        $user = self::findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                "id" => $userId
            ]
        ]);

        if($user){
            // check if subscription is free
            if($user->getSubscriptionStatus() == 'trial'){
                $createdPlus21 = date('Y-m-d', strtotime($user->getCreated()->format('Y-m-d'). '+14 days'));
                if($createdPlus21 <= $dateNow){
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }

        }else{
            return false;
        }





    }

}
