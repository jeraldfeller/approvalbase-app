<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 11/20/2018
 * Time: 11:38 AM
 */

namespace Aiden\Models;


class Billing extends _BaseModel
{
    /**
     * @Identity
     * @Primary
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
    protected $charge_id;


    /**
     * @Column(type="string", nullable=false)
     */
    protected $invoice_id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $invoice;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $date_created;

    /**
     * @Column(type="double", nullable=false)
     */
    protected $amount;


    /**
     * @Column(type="string", nullable=false)
     */
    protected $subscription_start_date;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $subscription_end_date;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $status;

    /**
     * Returns the database table name
     * @return string
     */
    public function getSource() {

        return 'billing';

    }

    /**
     * Sets the database relations within the app
     */
    public function initialize() {
        $this->belongsTo('users_id', 'Aiden\Models\Users', 'id', ['alias' => 'Users']);
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
    public function getUsersId() {

        return $this->users_id;

    }

    /**
     * Sets the council id
     * @param int $council_id
     */
    public function setUsersId(int $users_id) {

        $this->users_id = $users_id;
    }

    /**
     * Returns the email address
     * @return string
     */
    public function getChargeId() {
        return $this->charge_id;
    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setChargeId(string $charge_id) {
        $this->charge_id = $charge_id;
    }

    /**
     * Returns the email address
     * @return string
     */
    public function getInvoiceId() {
        return $this->invoice_id;
    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setInvoiceId(string $invoice_id) {
        $this->invoice_id = $invoice_id;
    }


    /**
     * Returns the email address
     * @return string
     */
    public function getInvoice() {
        return $this->invoice;
    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setInvoice(string $invoice) {
        $this->invoice = $invoice;
    }

    /**
     * Returns the email address
     * @return double
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Sets the email address
     * @param double $email
     */
    public function setAmount(string $amount) {
        $this->amount = $amount;
    }


    /**
     * Returns the email address
     * @return string
     */
    public function getDateCreated() {
        $dateCreated = \DateTime::createFromFormat('Y-m-d H:i:s', $this->date_created);
        return  $dateCreated;
    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setDateCreated(\DateTime $date_created) {
        $this->date_created = $date_created->format('Y-m-d H:i:s');
    }

    /**
     * Returns the email address
     * @return string
     */
    public function getSubscriptionStartDate() {
        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $this->subscription_start_date);
        return $startDate;
    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setSubscriptionStartDate(\DateTime $subscription_start_date) {
        $this->subscription_start_date = $subscription_start_date->format('Y-m-d H:i:s');
    }


    /**
     * Returns the email address
     * @return string
     */
    public function getSubscriptionEndDate() {
        $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', $this->subscription_end_date);
        return $endDate;
    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setSubscriptionEndDate(\DateTime $subscription_end_date) {
        $this->subscription_end_date = $subscription_end_date->format('Y-m-d H:i:s');
    }

    /**
     * Returns the email address
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Sets the email address
     * @param string $email
     */
    public function setStatus(string $status) {
        $this->status = $status;
    }

}