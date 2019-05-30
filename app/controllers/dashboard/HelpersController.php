<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 5/10/2019
 * Time: 2:05 PM
 */

namespace Aiden\Controllers;

use Aiden\Models\Das;
use Aiden\Models\DasAddresses;
use Aiden\Models\DasDocuments;
use Aiden\Models\DasParties;
use Aiden\Models\DasUsers;
use Aiden\Models\DasUsersNotes;
use Aiden\Models\DasUsersSearch;
use Aiden\Models\Users;
use Aiden\Models\Councils;
use Aiden\Models\UsersShareDa;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;
use Aiden\Models\Admin;
use Aiden\Models\MetaData;


class HelpersController extends _BaseController
{
    public function shareDaAction()
    {
        $usersId = $this->getUser()->getId();
        $email = $this->getUser()->getEmail();
        $name = $this->getUser()->getLastName() . ', ' . $this->getUser()->getName();
        $fullName = $this->getUser()->getName() . ' ' . $this->getUser()->getLastName();
        $emails = $this->request->getPost('emails');
        $dasId = $this->request->getPost('dasId');

        // check email counts
        // DA can be only shared twice per day and max 20 emails per day
        $emailCountObj = UsersShareDa::getTotalMailCount($dasId, $usersId);

        if ($emailCountObj['totalMailCount'] != 20) {
            if (($emailCountObj['totalMailCount'] + count($emails)) <= 20) {
                if ($emailCountObj['daShareCount'] < 2) {
                    $da = Das::findFirst([
                        'conditions' => 'id = :id:',
                        'bind' => ['id' => $dasId]
                    ]);
                    // format lodge date
                    $da->lodge_date_str = $da->lodge_date->format('d/m/Y');
                    // format cost
                    $da->estimated_cost_formatted = number_format($da->estimated_cost);
                    $council = Councils::findFirst([
                        'conditions' => 'id = :councilId:',
                        'bind' => [
                            'councilId' => $da->getCouncilId()
                        ]
                    ]);
                    $docs = DasDocuments::find([
                        'conditions' => 'das_id = :id:',
                        'bind' => ['id' => $dasId]
                    ]);

                    $address = DasAddresses::find([
                        'conditions' => 'das_id = :id:',
                        'bind' => ['id' => $dasId]
                    ]);
                    $parties = DasParties::find([
                        'conditions' => 'das_id = :id:',
                        'bind' => ['id' => $dasId]
                    ]);


                    // record share action
                    UsersShareDa::recordDaMail($dasId, $usersId, $emails);
                    $emailCountObj = null;
                    return json_encode(\Aiden\Models\Email::shareDaEmail($email, $fullName, $name, $emails, $council, $da, $docs, $address, $parties));
                } else {
                    $emailCountObj = null;
                    return json_encode([
                        'status' => false,
                        'message' => 'Project can only be shared twice per day.'
                    ]);
                }
            } else {
                $emailCountObj = null;
                return json_encode([
                    'status' => false,
                    'message' => 'Only ' . (20 - $emailCountObj['totalMailCount']) . ' email(s) allowed to share for this day.'
                ]);
            }
        } else {
            $emailCountObj = null;
            return json_encode([
                'status' => false,
                'message' => 'You have reached 20 emails limit for today.'
            ]);
        }
    }

    public function fixCouncilUrlAction()
    {
        $das = Das::find([
            'conditions' => 'council_id = :councilId: AND council_url IS NULL',
            'bind' => [
                'councilId' => 3
            ]
        ]);

        if(count($das) > 0){
            foreach ($das as $row){
                $councilUrl = 'https://eservices.blacktown.nsw.gov.au/T1PRProd/WebApps/eProperty/P1/eTrack/eTrackApplicationDetails.aspx?r=BCC.P1.WEBGUEST&f=%24P1.ETR.APPDET.VIW&ApplicationId='.$row->getCouncilReference();
                echo $councilUrl . '<br>';
                $row->setCouncilUrl($councilUrl);
                $row->save();
            }

        }
        $das = null;
        return true;
    }

    public function fixImageUrlAction(){
        $councils = Councils::find();
        foreach ($councils as $council){
            $logoUrl = $council->getLogoUrl();
            $newLogoUrl = str_replace('http', 'https', $logoUrl);
            echo $newLogoUrl . '<br>';
            $council->setLogoUrl($newLogoUrl);
            $council->save();
        }
        $councils = null;

        $users = Users::find();
        foreach($users as $user){
            $imageUrl = $user->getImageUrl();
            $newImageUrl = str_replace('http', 'https', $imageUrl);
            echo $newImageUrl . '<br>';
            $user->setImageUrl($newImageUrl);
            $user->save();
        }
        $users = null;

        return true;
    }


    public function getSubscriptionPlanAction(){
        $stripe = new Stripe();
        $stripe::setApiKey(Admin::getApiKeyBySource(STRIPE_ENV)['secretKey']);

        $list = \Stripe\Plan::all(["limit" => 10]);

        $stripe = null;
        echo json_encode($list);
    }

    public function removeStripeCustomersAction(){
        $stripe = new Stripe();
        $stripe::setApiKey(Admin::getApiKeyBySource(STRIPE_ENV)['secretKey']);

        $list = \Stripe\Customer::all(["limit" => 20]);
        $data = $list->data;
        $stripe = null;
        for($i = 0; $i < count($data); $i++){
            $customerId = $data[$i]->id;
            echo $customerId . '<br>';
            $customer = \Stripe\Customer::retrieve($customerId);
            $customer->delete();
        }
    }

    public function resetNorthSydneyDocsAction(){
        $das = new Das();
        $sql = 'SELECT dd.* FROM das_documents dd, das d WHERE d.id = dd.das_id AND d.council_id = 20';
        $result = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, [], [])
        );
        foreach ($result as $row) {
            $docID = $row->id;
            $dasDoc = DasDocuments::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $docID
                ]
            ]);

            if($dasDoc){
                $dasDoc->setAs3Url('');
                $dasDoc->setAs3Processed(0);
                $dasDoc->setStatus(0);
                $dasDoc->save();
            }

        }

        $dasDoc = null;
        $das = null;
        $result = null;
    }
}