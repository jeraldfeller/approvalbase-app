<?php

namespace Aiden\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;

class EditLeadForm extends Form {

    public function initialize() {

        // Applicant
        $applicant = new Text('input_applicant');
        $applicant
                ->addValidators([
                    new \Phalcon\Validation\Validator\StringLength([
                        'max' => 255,
                        'min' => 1,
                        'messageMaximum' => 'The specified applicant is too long.',
                        'messageMinimum' => 'The specified applicant is too short.',
                        'cancelOnFail' => true,
                            ])
        ]);
        $this->add($applicant);

        // First name
        $firstName = new Text('input_firstname');
        $firstName
                ->addValidators([
                    new \Phalcon\Validation\Validator\StringLength([
                        'max' => 255,
                        'min' => 1,
                        'messageMaximum' => 'The specified first name is too long.',
                        'messageMinimum' => 'The specified first name is too short.',
                        'cancelOnFail' => true,
                        'allowEmpty' => true,
                            ])
        ]);
        $this->add($firstName);

        // Last name
        $lastName = new Text('input_lastname');
        $lastName
                ->addValidators([
                    new \Phalcon\Validation\Validator\StringLength([
                        'max' => 255,
                        'min' => 1,
                        'messageMaximum' => 'The specified last name is too long.',
                        'messageMinimum' => 'The specified last name is too short.',
                        'cancelOnFail' => true,
                        'allowEmpty' => true,
                            ])
        ]);
        $this->add($lastName);

        // Phone
        $phone = new Text('input_phone');
        $phone
                ->addValidators([
                    new \Phalcon\Validation\Validator\StringLength([
                        'max' => 15,
                        'min' => 1,
                        'messageMaximum' => 'The specified phone is too long.',
                        'messageMinimum' => 'The specified phone is too short.',
                        'cancelOnFail' => true,
                        'allowEmpty' => true,
                            ])
        ]);
        $this->add($phone);

        // Email
        $email = new Text('input_email');
        $email
                ->addValidators([
                    new \Phalcon\Validation\Validator\StringLength([
                        'max' => 255,
                        'min' => 1,
                        'messageMaximum' => 'The specified email is too long.',
                        'messageMinimum' => 'The specified email is too short.',
                        'cancelOnFail' => true,
                        'allowEmpty' => true,
                            ]),
                    new \Phalcon\Validation\Validator\Email([
                        'message' => 'Please use a valid email address.',
                        'cancelOnFail' => true,
                        'allowEmpty' => true,
                            ]),
        ]);
        $this->add($email);

        // LinkedIn
        $linkedIn = new Text('input_linkedin');
        $linkedIn
                ->addValidators([
                    new \Phalcon\Validation\Validator\StringLength([
                        'max' => 255,
                        'min' => 1,
                        'messageMaximum' => 'The specified LinkedIn URL is too long.',
                        'messageMinimum' => 'The specified LinkedIn URL is too short.',
                        'cancelOnFail' => true,
                        'allowEmpty' => true,
                            ])
        ]);
        $this->add($linkedIn);

        // Submit
        $submit = new \Phalcon\Forms\Element\Submit('submit', [
            'value' => 'Submit',
        ]);
        $this->add($submit);

    }

}
