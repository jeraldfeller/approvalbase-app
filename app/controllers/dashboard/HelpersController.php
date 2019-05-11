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

class HelpersController extends _BaseController
{
    public function shareDaAction(){
        $email = $this->getUser()->getEmail();
        $name = $this->getUser()->getLastName().', '. $this->getUser()->getName();
        $emails = $this->request->getPost('emails');
        $emailsTo = implode(',', $emails);
        $dasId = $this->request->getPost('dasId');
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
        return json_encode(\Aiden\Models\Email::shareDaEmail($email, $name, $emailsTo, $council, $da, $docs, $address, $parties));
    }
}