<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 12/5/2018
 * Time: 3:06 PM
 */

namespace Aiden\Models;


class Email extends _BaseModel
{

    public static function shareDaEmail($email, $fullName, $name, $emailsTo, $council, $da, $docs, $address, $parties){
        $di = \Phalcon\DI::getDefault();
        $view = $di->getView();
        $view->start();
        $view->setVars([
            'council' => $council,
            'da' => $da,
            'docs' => $docs,
            'address' => $address,
            'parties' => $parties,
            'name' => $fullName
        ]);
        $view->setTemplateAfter('share_da'); // template name
        $view->render('controller', 'action');
        $view->finish();

        $emailHtml = $view->getContent();


        $config = $di->getConfig();
        $postFields = [
            'from' => sprintf('%s <%s>', $name, $email),
            'subject' => 'ApprovalBase',
            'html' => $emailHtml,
            'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
            'to' => $emailsTo
        ];

        return self::sendEmail($postFields, $config);
    }

    public static function contactFormEmail($subject, $message, $email, $name){

        $di = \Phalcon\DI::getDefault();
        $view = $di->getView();
        $view->start();
        $view->setVars([
            'userEmail' => $email,
            'message' => $message,
            'name' => $name
        ]);
        $view->setTemplateAfter('contact_email'); // template name
        $view->render('controller', 'action');
        $view->finish();

        $emailHtml = $view->getContent();


        $config = $di->getConfig();
        $postFields = [
            'from' => sprintf('%s <%s>', $config->mailgun->mailFromName, $config->mailgun->mailFromEmail),
            'subject' => 'Support - ' . $subject,
            'html' => $emailHtml,
            'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
            'to' => $config->adminEmail
        ];

        return self::sendEmail($postFields, $config);

    }
    public static function signupNotification($user){
        $email = $user->email;
        $fname = $user->name;
        $lname = $user->last_name;
        $website = $user->website_url;
        $company = $user->company_name;
        $companyCity = $user->company_city;
        $companyCountry = $user->company_country;
        $solution = ucfirst($user->solution);


        $di = \Phalcon\DI::getDefault();
        $view = $di->getView();
        $view->start();
        $view->setVars([
            'userEmail' => $email,
            'fname' => $fname,
            'lname' => $lname,
            'website' => $website,
            'company' => $company,
            'companyCity' => $companyCity,
            'companyCountry' =>  $companyCountry,
            'solution' => $solution
        ]);
        $view->setTemplateAfter('signup_email'); // template name
        $view->render('controller', 'action');
        $view->finish();

        $emailHtml = $view->getContent();


        $config = $di->getConfig();
        $postFields = [
            'from' => sprintf('%s <%s>', $config->mailgun->mailFromName, $config->mailgun->mailFromEmail),
            'subject' => $config->mailgun->mailDigestSubject,
            'html' => $emailHtml,
            'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
            'to' => $config->adminEmail
        ];

        return self::sendEmail($postFields, $config);
    }

    public static function welcomeNotification($email, $firstName, $lastName, $verificationCode){
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();
        $view = $di->getView();
        $view->start();
        $view->setVars([
            'userEmail' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'url' => $config->baseUri.'/dashboard?vc='.$verificationCode
        ]);
        $view->setTemplateAfter('welcome_email'); // template name
        $view->render('controller', 'action');
        $view->finish();

        $emailHtml = $view->getContent();


        $config = $di->getConfig();
        $postFields = [
            'from' => sprintf('%s <%s>', $config->mailgun->mailFromName, $config->mailgun->mailFromEmail),
            'subject' => 'Welcome to ApprovalBase!',
            'html' => $emailHtml,
            'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
            'to' => $email
        ];

        return self::sendEmail($postFields, $config);
    }


    public static function forgotPasswordEmail($email, $firstName, $verificationCode, $usersId){
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();
        $view = $di->getView();
        $view->start();
        $view->setVars([
            'userEmail' => $email,
            'firstName' => $firstName,
            'url' => $config->baseUri.'change-password?vc='.$verificationCode.'&a='.$usersId,
        ]);
        $view->setTemplateAfter('forgot_password'); // template name
        $view->render('controller', 'action');
        $view->finish();

        $emailHtml = $view->getContent();


        $config = $di->getConfig();
        $postFields = [
            'from' => sprintf('%s <%s>', $config->mailgun->mailFromName, $config->mailgun->mailFromEmail),
            'subject' => 'Reset your ApprovalBase password',
            'html' => $emailHtml,
            'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
            'to' => $email
        ];

        return self::sendEmail($postFields, $config);
    }

    public static function subscriptionEmailNotification($fname, $lname, $email, $amount, $transactionId, $cardNumber){
        $di = \Phalcon\DI::getDefault();
        $view = $di->getView();
        $view->start();
        $view->setVars([
            'fname' => $fname,
            'lname' => $lname,
            'userEmail' => $email,
            'amount' => $amount,
            'transactionId' => $transactionId,
            'paymentDate' => date('M d, Y'),
            'cardNumber' => $cardNumber
        ]);
        $view->setTemplateAfter('subscription_email'); // template name
        $view->render('controller', 'action');
        $view->finish();

        $emailHtml = $view->getContent();


        $config = $di->getConfig();
        $postFields = [
            'from' => sprintf('%s <%s>', $config->mailgun->mailFromName, $config->mailgun->mailFromEmail),
            'subject' => 'ApprovalBase Subscription',
            'html' => $emailHtml,
            'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
            'to' => $email
        ];


        return self::sendEmail($postFields, $config);
    }


    public static function subscriptionExpirationNotification($action, $fname, $lname, $email){
        $di = \Phalcon\DI::getDefault();
        $config = $di->getConfig();
        $view = $di->getView();
        $view->start();
        $view->setVars([
            'fname' => $fname,
            'lname' => $lname,
            'userEmail' => $email,
            'phrase' => ($action == 'trial' ? 'Your free trial has expired. We hope you were as amazed by our product as we are. Hit the button below to create your paid account and start driving growth!' : 'Your monthly subscription is about to expire. We hope you were as amazed by our product as we are. Hit the button below to subscribe!'),
            'billingUrl' => $config->baseUri.'billing'
        ]);
        $view->setTemplateAfter('subscription_notification'); // template name
        $view->render('controller', 'action');
        $view->finish();

        $emailHtml = $view->getContent();


        $config = $di->getConfig();
        $postFields = [
            'from' => sprintf('%s <%s>', $config->mailgun->mailFromName, $config->mailgun->mailFromEmail),
            'subject' => 'ApprovalBase Subscription Notification',
            'html' => $emailHtml,
            'text' => strip_tags(\Aiden\Classes\SwissKnife::br2nl($emailHtml)),
            'to' => $email
        ];


        return self::sendEmail($postFields, $config);
    }




    public static function dailyNotificationEmail($config, $postFields){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $config->mailgun->mailgunApiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/' . $config->mailgun->mailgunDomain . '/messages');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$config->dev);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$config->dev);

        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);


        // Error
        if ($info['http_code'] != 200) {

            $message = sprintf('Could not send email, HTTP STATUS [%s]', $info['http_code']);
            return false;
        }

        // Attempt to parse JSON
        $json = json_decode($output);
        if ($json === null) {
            $message = 'Mailgun returned a non-Json response';
            return false;
        }

        // After we've received confirmation from Mailgun, set matches as
        // processed so we only get fresh matches next time.
        if ($json->message == 'Queued. Thank you.') {
            return true;
        } else {
            return false;
        }
    }

    public static function sendEmail($postFields, $config){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $config->mailgun->mailgunApiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/' . $config->mailgun->mailgunDomain . '/messages');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$config->dev);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$config->dev);

        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);


        // Error
        if ($info['http_code'] != 200) {
            $message = sprintf('Could not send email, HTTP STATUS [%s]', $info['http_code']);
            return false;
        }

        // Attempt to parse JSON
        $json = json_decode($output);
        if ($json === null) {
            $message = 'Mailgun returned a non-Json response';
            return false;
        }

        // After we've received confirmation from Mailgun, set matches as
        // processed so we only get fresh matches next time.

        if ($json->message == 'Queued. Thank you.') {
            // set DA processed to true
            return true;
        }
        else {
            return false;
        }

    }
}