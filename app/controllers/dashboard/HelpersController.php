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


class HelpersController extends _BaseController
{
    public function shareDaAction(){
        $usersId = $this->getUser()->getId();
        $email = $this->getUser()->getEmail();
        $name = $this->getUser()->getLastName().', '. $this->getUser()->getName();
        $fullName = $this->getUser()->getName() . ' ' . $this->getUser()->getLastName();
        $emails = $this->request->getPost('emails');
        $emailsTo = implode(',', $emails);
        $dasId = $this->request->getPost('dasId');

        // check email counts
        // DA can be only shared twice per day and max 20 emails per day
        $emailCountObj = UsersShareDa::getTotalMailCount($dasId, $usersId);

        if($emailCountObj['totalMailCount'] != 20){
            if(($emailCountObj['totalMailCount'] + count($emails)) <= 20){
                if($emailCountObj['daShareCount'] < 2){
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

                    return json_encode(\Aiden\Models\Email::shareDaEmail($email, $fullName, $name, $emailsTo, $council, $da, $docs, $address, $parties));
                }else{
                    return json_encode([
                        'status' => false,
                        'message' => 'Project can only be shared twice per day.'
                    ]);
                }
            }else{
                return json_encode([
                    'status' => false,
                    'message' => 'Only '. (20-$emailCountObj['totalMailCount'] ) .' email(s) allowed to share for this day.'
                ]);
            }
        }else{
            return json_encode([
                'status' => false,
                'message' => 'You have reached 20 emails limit for today.'
            ]);
        }
    }
}