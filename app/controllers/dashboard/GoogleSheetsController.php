<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 5/15/2019
 * Time: 6:05 AM
 */

namespace Aiden\Controllers;
use Aiden\Models\Users;
define('STDIN', fopen("php://stdin", "r"));

class GoogleSheetsController extends _BaseController
{
    private $spreadSheetId = '1MA9jcFS90j7upVXCKHQFOnkqw0rL5ENKculXaHnbpYU';



    public function updateUsers()
    {
        // clear the sheets first
        $this->clearUsers();
        $users = Users::find();
        $client = \Aiden\Models\GoogleSheets::getClient();
        $service = new \Google_Service_Sheets($client);
        $range = 'Users!A:G';
        $param = [
            "valueInputOption" => "USER_ENTERED",
            "insertDataOption" => "INSERT_ROWS",
        ];

        if($users){
            $userArr[] = [
                "First Name",
                "Last Name",
                "Email",
                "Website Url",
                "Company",
                "Status",
                "Solution"
            ];
            foreach ($users as $user){
                $userArr[] = [
                    $user->getName(),
                    $user->getLastName(),
                    $user->getEmail(),
                    $user->getWebsiteUrl(),
                    $user->getCompanyName(),
                    $user->getSubscriptionStatus(),
                    $user->getSolution()
                ];
            }
            $values = ["values" => $userArr
            ];

            $requestBody = new \Google_Service_Sheets_ValueRange($values);
            $response = $service->spreadsheets_values->append($this->spreadSheetId, $range, $requestBody, $param);

        }
        return true;
    }

    public function clearUsers(){
        $client = \Aiden\Models\GoogleSheets::getClient();
        $service = new \Google_Service_Sheets($client);
        $range = 'Users!A:G';
        $requestBody = new \Google_Service_Sheets_ClearValuesRequest();

        $response = $service->spreadsheets_values->clear($this->spreadSheetId, $range, $requestBody);

        return true;
    }
}