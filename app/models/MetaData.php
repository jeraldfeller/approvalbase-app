<?php
/**
 * Created by PhpStorm.
 * User: jeral
 * Date: 5/28/2019
 * Time: 10:15 AM
 */

namespace Aiden\Models;


class MetaData extends _BaseModel
{

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    protected $id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $title;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $value;


    /**
     * Returns the council reference
     */
    public function getTitle() {
        return $this->title;

    }

    /**
     * Sets the council reference
     * @param string $council_reference
     */
    public function setTitle(string $title) {

        $this->title = $title;

    }


    /**
     * Returns the council reference
     */
    public function getValue() {
        return $this->value;

    }

    /**
     * Sets the council reference
     * @param string $council_reference
     */
    public function setValue(string $value) {

        $this->value = $value;

    }


    public static function getMetaDataByTitle($title){
        $md = self::findFirst([
            'conditions' => 'title = :title:',
            'bind' => [
                'title' => $title
            ]
        ]);

        if($md){
            $value = $md->getValue();
        }else{
            $value = false;
        }

        $md = null;
        return $value;
    }

}