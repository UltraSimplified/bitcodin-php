<?php
/**
 * Created by PhpStorm.
 * User: cwioro
 * Date: 18.06.15
 * Time: 13:20
 */

namespace bitcodin;

use bitcodin\exceptions\BitcodinException;

/**
 * Class Bitcodin
 * @package bitcodin
 */
class Bitcodin
{
    const DEFAULT_BASE_URL = 'http://portal.bitcodin.com/api';
    const API_KEY_FIELD_NAME = 'bitcodin-api-key';

    /**
     * @var string|null
     */
    static private $apiKey = NULL;

    /**
     * @var string
     */
    static private $baseUrl = self::DEFAULT_BASE_URL;

    /**
     * @param $token
     */
    public static function setApiToken($token)
    {
        self::$apiKey = $token;
    }

    /**
     * @return null|string
     * @throws BitcodinException
     */
    public static function getApiToken()
    {
        if (self::$apiKey === NULL)
            throw new BitcodinException('Api token is not set!');

        return self::$apiKey;
    }

    /**
     * @param $baseUrl
     */
    public static function setBaseUrl($baseUrl)
    {
        self::$baseUrl = $baseUrl;
    }

    /**
     * @return string
     * @throws BitcodinException
     */
    public static function getBaseUrl()
    {
        if (self::$baseUrl === NULL)
            throw new BitcodinException('Base url is not set!');

        return self::$baseUrl;
    }
}