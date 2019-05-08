<?php
/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 11/20/2018
 * Time: 7:55 AM
 */

namespace Aiden\Models;


class Admin extends _BaseModel
{
    /**
     * @Identity
     * @Primary
     * @Column(type="integer", nullable=false)
     */
    protected $id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $api_source;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $api_key;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $secret_key;

    public function getSource()
    {
        return 'admin';
    }

    /**
     * Returns the api source
     */
    public function getApiSource()
    {
        return $this->api_source;
    }

    public function setApiSource(string $api_source)
    {
        $this->api_source = $api_source;
    }

    /**
     * Returns the  api key
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    public function setApiKey(string $api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * Returns the  api secret key
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    public function setSecretKey(string $secret_key)
    {
        $this->secret_key = $secret_key;
    }

    public static function getApiKeyBySource($source)
    {
        $api = self::findFirst([
            'conditions' => 'api_source = :api_source:',
            'bind' => [
                "api_source" => $source
            ]
        ]);

        if ($api) {
            return array(
                'apiKey' => $api->getApiKey(),
                'secretKey' => $api->getSecretKey()
            );
        } else {
            return false;
        }
    }
}