<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 5/15/2019
 * Time: 7:37 AM
 */

namespace Aiden\Models;


class GoogleSheets extends _BaseModel
{

    public static function getClient()
    {

        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets AB Users');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig('credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public static function appendUsers($user = null, $client)
    {
        $spreadSheetId = '1MA9jcFS90j7upVXCKHQFOnkqw0rL5ENKculXaHnbpYU';
        $service = new \Google_Service_Sheets($client);
        $range = 'Users!A:G';
        $param = [
            "valueInputOption" => "USER_ENTERED",
            "insertDataOption" => "INSERT_ROWS",
        ];
        $values = ["values" => [
            [
                $user->getName(),
                $user->getLastName(),
                $user->getEmail(),
                $user->getWebsiteUrl(),
                $user->getCompanyName(),
                $user->getSubscriptionStatus(),
                $user->getSolution()
            ]
        ]
        ];

        $requestBody = new \Google_Service_Sheets_ValueRange($values);
        $response = $service->spreadsheets_values->append($spreadSheetId, $range, $requestBody, $param);

// TODO: Change code below to process the `response` object:
        echo '<pre>', var_export($response, true), '</pre>', "\n";
    }
}