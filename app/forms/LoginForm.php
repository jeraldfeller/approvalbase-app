<?php

namespace Aiden\Forms;

class LoginForm extends \Phalcon\Forms\Form {

    public function initialize() {

        // Email
        $email = new \Phalcon\Forms\Element\Email('email', [
            'class' => 'form-control',
            'placeholder' => 'Enter an email address',
            'autofocus' => '',
            'required' => '',
        ]);
        $email
                ->setLabel('Email:')
                ->addValidators([
                    new \Phalcon\Validation\Validator\StringLength([
                        'max' => 254,
                        'min' => 1,
                        'messageMaximum' => 'The specified email is too long.',
                        'messageMinimum' => 'The specified email is too short.',
                        'cancelOnFail' => true,
                            ]),
                    new \Phalcon\Validation\Validator\Email([
                        'message' => 'Please use a valid email address.',
                        'cancelOnFail' => true,
                            ]),
        ]);
        $this->add($email);

        // Password
        $password = new \Phalcon\Forms\Element\Password('password', [
            'class' => 'form-control',
            'placeholder' => 'Enter a password',
            'required' => '',
        ]);
        $password
                ->setLabel('Password:')
                ->addValidator(
                        new \Phalcon\Validation\Validator\PresenceOf([
                    'message' => 'Please enter your password.',
                    'cancelOnFail' => true,
                        ])
        );
        $this->add($password);

        // Submit
        $submit = new \Phalcon\Forms\Element\Submit('submit', [
            'value' => 'Submit',
            'class' => 'btn btn-success'
        ]);
        $this->add($submit);

    }

}
