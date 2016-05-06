<?php
/**
 * Created by PhpStorm.
 * User: cwioro
 * Date: 18.06.15
 * Time: 13:38
 */

namespace bitcodin;

use bitcodin\exceptions\BitcodinException;
use bitcodin\exceptions\BitcodinResourceNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Message\Response;

/**
 * Class ApiResource
 * @package bitcodin
 */
abstract class ApiResource extends \stdClass
{
    /**
     * @param \stdClass $class
     */
    public function __construct(\stdClass $class)
    {
        $this->_copy($class);
    }

    /**
     * @var Client|null
     */
    private static $client = NULL;

    /**
     * @var array|null
     */
    private static $headers = NULL;

    /**
     * @return Client|null
     */
    protected static function getClient()
    {
        if (self::$client === NULL)
            self::$client = new Client(['base_uri' => Bitcodin::getBaseUrl()]);

        return self::$client;
    }

    /**
     * @return array|null
     * @throws BitcodinException
     */
    protected static function getHeaders()
    {
        if (self::$headers === NULL)
            self::$headers = ['Content-type'               => 'application/json',
                              'Accept'                     => 'application/json',
                              Bitcodin::API_KEY_FIELD_NAME => Bitcodin::getApiToken()];

        return self::$headers;
    }

    /**
     * @param $obj
     */
    protected function _copy($obj)
    {
        foreach (get_object_vars($obj) as $key => $val) {
            $this->$key = $val;
        }
    }

    static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }


    /**
     * @param $url
     * @param $body
     * @param $expectedStatusCode
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     * @throws exceptions\BitcodinException
     */
    protected static function _postRequest($url, $body, $expectedStatusCode)
    {
        $httpClient = self::getClient();
        try {
            $res = $httpClient->post(Bitcodin::getBaseUrl() . $url, ['headers' => self::getHeaders(),
                                                                     'body'    => $body]);
        } catch (BadResponseException $ex) {
            $res = $ex->getResponse();
        }
        //self::_checkExpectedStatus($res, $expectedStatusCode);

        return $res;
    }

    /**
     * @param $url
     * @param $body
     * @param $expectedStatusCode
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     * @throws BitcodinException
     * @throws BitcodinResourceNotFoundException
     */
    protected static function _patchRequest($url, $body = null, $expectedStatusCode)
    {
        $httpClient = self::getClient();
        try {
            $res = $httpClient->patch(Bitcodin::getBaseUrl() . $url, ['headers' => self::getHeaders(),
                                                                      'body'    => $body]);
        } catch (BadResponseException $ex) {
            $res = $ex->getResponse();
        }

        self::_checkExpectedStatus($res, $expectedStatusCode);
        return $res;
    }

    /**
     * @param $url
     * @param $expectedStatusCode
     * @param $query
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     * @throws BitcodinException
     */
    protected static function _getRequest($url, $expectedStatusCode, $query=array())
    {
        try {
            $client = self::getClient();
            $res = $client->get(Bitcodin::getBaseUrl() . $url, ['headers' => self::getHeaders(), 'query' => $query]);
        } catch (BadResponseException $ex) {
            $res = $ex->getResponse();
        }
       // self::_checkExpectedStatus($res, $expectedStatusCode);
        return $res;
    }

    /**
     * @param $url
     * @param $expectedStatusCode
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     * @throws BitcodinException
     */
    protected static function _deleteRequest($url, $expectedStatusCode)
    {
        $httpClient = self::getClient();
        try {
            $res = $httpClient->delete(Bitcodin::getBaseUrl() . $url, ['headers' => self::getHeaders()]);
        } catch (BadResponseException $ex) {
            $res = $ex->getResponse();
        }
        self::_checkExpectedStatus($res, $expectedStatusCode);
        return $res;
    }

    /**
     * @param Response $response
     * @param $status
     * @throws BitcodinException
     * @throws BitcodinResourceNotFoundException
     */
    protected static function _checkExpectedStatus(Response $response, $status)
    {
        if ($response->getStatusCode() !== $status) {
            $responseDecoded = json_decode($response->getBody()->getContents());
            if ($response->getStatusCode() == 404)
                throw new BitcodinResourceNotFoundException($responseDecoded->message);
            elseif (isset($responseDecoded->message))
                throw new BitcodinException($responseDecoded->message);
            else
                throw new BitcodinException('Something went wrong during api request: Response status[' . $response->getStatusCode() . '] does not match expected status [' . $status . ']! Response: ' . $response->getBody()->getContents());
        }
    }
}
