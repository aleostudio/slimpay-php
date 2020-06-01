<?php
/**
 * This file is part of the SlimPay Iframe package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LunaLabs\SlimPayIframe;

// Package classes.
use LunaLabs\SlimPayIframe\Http\Client;
use LunaLabs\SlimPayIframe\Exceptions\SlimPayIframeException;
use GuzzleHttp\Exception\GuzzleException;


class SlimPayIframe
{
    /**
     * @var Client $client
     */
    protected $client;


    /**
     * SlimPay Iframe constructor.
     *
     * @param array  $config   SlimPay Iframe configuration.
     * @param Client $client   The Guzzle HTTP client.
     */
    public function __construct(array $config = null, Client $client = null)
    {
        if (is_null($client)) {
            if (is_null($config)) throw new SlimPayIframeException('The SlimPay Iframe auth configuration is missing');
            $client = new Client($config);
        }

        $this->client = $client;
    }


    /**
     * Sends to SlimPay a checkout request with the payment method set. This resource will return
     * an HAL JSON with all the referenced resources.
     *
     * @param  array $data
     * @return mixed
     * @throws SlimPayIframeException|GuzzleException
     */
    public function checkout(array $data)
    {
        return $this->client->request('POST', '/orders', [ 'json' => $data ])->toObject();
    }


    /**
     * Retrieves a resource by the given endpoint (it must have the authentication bearer).
     *
     * @param  string $endpoint
     * @return mixed
     * @throws SlimPayIframeException|GuzzleException
     */
    public function getResource(string $endpoint)
    {
        return $this->client->request('GET', $endpoint, [])->toObject();
    }
}
