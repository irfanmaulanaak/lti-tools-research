<?php

namespace IMSGlobal\LTI;

use IMSGlobal\LTI\Http;

/**
 * Class to represent an HTTP message
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.0
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class HTTPMessage
{

    /**
     * @var Http\ClientInterface The client used to send the request.
     */
    private static $httpClient;

    /**
     * True if message was sent successfully.
     * @var boolean $ok
     */
    public $ok = false;

    /**
     * Request body.
     * @var string|null $request
     */
    public $request = null;

    /**
     * Request headers.
     * @var string $requestHeaders
     */
    public $requestHeaders = '';

    /**
     * Response body.
     * @var string|null $response
     */
    public $response = null;

    /**
     * @var object|null Json decoded response data.
     */
    public $responseJson = null;

    /**
     * Response headers.
     * @var string $responseHeaders
     */
    public $responseHeaders = '';

    /**
     * Status of response (0 if undetermined).
     * @var int $status
     */
    public $status = 0;

    /**
     * Error message
     * @var string $error
     */
    public $error = '';

    /**
     * Request URL.
     * @var string $url
     */
    public $url = null;

    /**
     * Request method.
     * @var string|null $method
     */
    public $method = null;

    /**
     * Class constructor.
     * @param string $url    URL to send request to
     * @param string $method Request method to use (optional, default is GET)
     * @param mixed  $params Associative array of parameter values to be passed or message body (optional, default is none)
     * @param string $header Values to include in the request header (optional, default is none)
     */
    function __construct($url, $method = 'GET', $params = null, $header = null)
    {
        $this->url = $url;
        $this->method = strtoupper($method);
        if (is_array($params)) {
            $this->request = http_build_query($params);
        } else {
            $this->request = $params;
        }
        if (!empty($header)) {
            $this->requestHeaders = explode("\n", $header);
        }
    }

    /**
     * Set a HTTP client.
     * @param Http\ClientInterface|null $httpClient The HTTP client to use for sending message.
     */
    public static function setHttpClient(Http\ClientInterface $httpClient = null)
    {
        self::$httpClient = $httpClient;
    }

    /**
     * Retrieves the HTTP client used for sending the message. Creates a default client if one is not set.
     * @return Http\ClientInterface
     */
    public static function getHttpClient()
    {
        if (!self::$httpClient) {
            if (function_exists('curl_init')) {
                self::$httpClient = new Http\CurlClient();
            } elseif (ini_get('allow_url_fopen')) {
                self::$httpClient = new Http\StreamClient();
            } else {
                throw new \RuntimeException('Cannot create an HTTP client, because neither cURL or allow_url_fopen are enabled.');
            }
        }

        return self::$httpClient;
    }

    /**
     * Send the request to the target URL.
     * @return boolean True if the request was successful
     */
    public function send()
    {
        $this->ok = self::getHttpClient()->send($this);

        return $this->ok;
    }

}
