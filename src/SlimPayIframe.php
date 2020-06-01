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


    public function createOrders(array $data)
    {
        $response = $this->client->request('POST', '/alps#create-orders', [ 'base_url' => 'https://api.slimpay.net', 'json' => $data ])->toObject();

        $x = true;

        return $response;
    }
}
