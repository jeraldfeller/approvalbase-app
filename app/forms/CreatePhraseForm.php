<?php

namespace Aiden\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;

class CreatePhraseForm extends Form {

    public function initialize() {

        // Phrase
        $phrase = new Text('input_phrase');
        $phrase
                ->addValidators([
                    new \Phalcon\Validation\Validator\StringLength([
                        'max' => 255,
                        'min' => 1,
                        'messageMaximum' => 'The specified phrase is too long.',
                        'messageMinimum' => 'The specified phrase is too short.',
                        'cancelOnFail' => true,
                            ])
        ]);
        $this->add($phrase);

        // Case sensitive
        $caseSensitive = new Text('input_case_sensitive');
        $this->add($caseSensitive);

        // Literal search
        $literalSearch = new Text('input_literal_search');
        $this->add($literalSearch);

        // Submit
        $submit = new \Phalcon\Forms\Element\Submit('submit', [
            'value' => 'Submit',
        ]);
        $this->add($submit);

    }

}
