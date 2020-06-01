<?php
/**
 * This file is part of the SlimPay Iframe package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LunaLabs\SlimPayIframe\Http;

// Package classes.
use LunaLabs\SlimPayIframe\Http\Response;
use LunaLabs\SlimPayIframe\Exceptions\SlimPayIframeException;

// External packages.
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;


class Client
{
    /** @var GuzzleClient */
    private $client;

    /** @var array */
    private $config;

    /** @var object */
    private $token;

    /** @var string */
    private $userAgent = 'LunaLabs SlimPay Iframe 0.1 Client';


    /**
     * Client constructor.
     *
     * @param array $config   Configuration array.
     */
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->client = $this->getClient();
    }


    /**
     * Makes an API request through a valid access token to the given url.
     *
     * @param  string $method
     * @param  string $endpoint
     * @param  array  $params
     * @return object
     * @throws GuzzleException
     * @throws SlimPayIframeException
     */
    public function request(string $method, string $endpoint, array $params): object
    {
        // Adds the access token to all the requests an set the content type to JSON.
        $data = [
            'headers' => [
                'User-Agent'    => $this->userAgent,
                'Accept'        => 'application/hal+json; profile="'.$this->config['profileUri'].'/alps/'.$this->config['apiVersion'].'"',
                'Content-type'  => 'application/json',
                'Authorization' => 'Bearer '.$this->getToken()->access_token
            ]
        ];

        // Adds optionals parameters if set.
        foreach ($params as $param => $value) {
            $data[$param] = $value;
        }

        try {
            $rawResponse = $this->getClient()->request($method, $endpoint, $data);

        } catch (ConnectException $e) {
            // Connection exception (no internet, timeout...).
            throw new SlimPayIframeException('Connect exception (code: '.$e->getCode().'): ' . $e->getMessage());

        } catch (ClientException $e) {
            // 400 level errors.
            // If the request returns a 401, means that we have an expired or invalid access token,
            // so we force to obtain a new one through the refresh token and update the token expiry.
            if ($e->getCode() == 401) {
                $this->token = $this->getToken();
                return $this->request($method, $endpoint, $params);
            }
            throw new SlimPayIframeException('Client exception (code: '.$e->getCode().'): ' . $e->getMessage());

        } catch (ServerException $e) {
            // 500 level errors.
            throw new SlimPayIframeException('Server exception (code: '.$e->getCode().'): ' . $e->getMessage());

        } catch (TooManyRedirectsException $e) {
            // 301 redirects errors.
            throw new SlimPayIframeException('Too many redirects exception (code: '.$e->getCode().'): ' . $e->getMessage());

        } catch (GuzzleException $e) {
            throw new SlimPayIframeException('Fatal error (code: '.$e->getCode().'): ' . $e->getMessage());
        }

        return new Response($rawResponse);
    }


    /**
     * Returns a valid Guzzle HTTP client.
     *
     * @return GuzzleClient $client
     */
    private function getClient(): GuzzleClient
    {
        return $this->client ?? new GuzzleClient([ 'base_uri' => $this->config['baseUri'] ]);
    }


    /**
     * Retrieves a valid token object from SlimPay.
     *
     * @return object $token
     */
    public function getToken(): object
    {
        // The token already exists. Let's go to check if it is still valid.
        if ($this->token) {

            // If the token is expired, simply invalidate the current one and recreate it.
            if (time() >= $this->token->expired_at) {
                $this->token = null;
                $this->getToken();
            }

            return $this->token;
        }

        // The token not exists, so we need to ask a new one to SlimPay.
        $rawResponse = $this->getClient()->post('/oauth/token', [
            'headers' => [
                'User-Agent'    => $this->userAgent,
                'Accept'        => 'application/json',
                'Content-type'  => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '.base64_encode($this->config['appId'] . ':' . $this->config['appSecret']),
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'scope'      => 'api'
            ]
        ]);

        $token = json_decode($rawResponse->getBody());

        // Adding the expiration date to the token object just for convenience.
        $token->created_at = time();
        $token->expired_at = $token->created_at + $token->expires_in;

        $this->setToken($token);

        return $token;
    }


    /**
     * Sets the given token in the instance.
     *
     * @param $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }


    /**
     * Retrieves the API version from the config array.
     *
     * @return mixed
     */
    public function getApiVersion()
    {
        return $this->config['apiVersion'];
    }
}
