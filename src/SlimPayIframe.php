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
    private $client;

    /**
     * @var array $config
     */
    private $config;


    /**
     * SlimPay Iframe constructor.
     *
     * @param  array  $config SlimPay Iframe configuration.
     * @param  Client $client The Guzzle HTTP client.
     * @throws SlimPayIframeException
     */
    public function __construct(array $config = null, Client $client = null)
    {
        if (is_null($client)) {
            if (is_null($config)) throw new SlimPayIframeException('The SlimPay Iframe auth configuration is missing');
            $client = new Client($config);
        }

        $this->config = $config;
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
     * @param  array $params
     * @return mixed
     * @throws SlimPayIframeException|GuzzleException
     */
    public function getResource(string $endpoint, array $params = [])
    {
        return $this->client->request('GET', $endpoint, $params)->toObject();
    }


    /**
     * Returns the checkout Iframe HTML code or redirect to the checkout page.
     *
     * @param  object $response
     * @return void
     * @throws SlimPayIframeException|GuzzleException
     */
    public function showCheckoutPage(object $response): void
    {
        $resourceLinks = [
            'userApproval'     => $this->config['profileUri'].'/alps#user-approval',
            'extendedApproval' => $this->config['profileUri'].'/alps#extended-user-approval'
        ];

        if ($this->config['mode'] == 'iframe') {

            $link    = $response->_links->{$resourceLinks['extendedApproval']}->href;
            $link    = str_replace('{?mode}', '', $link);
            $encoded = $this->getResource($link, ['mode' => 'iframeembedded']);
            $html    = base64_decode($encoded->content);
            echo $html;

        } else {
            header('Location: ' . $response->_links->{$resourceLinks['userApproval']}->href);
        }
    }


    /**
     * Checks if the given response is valid.
     *
     * @param  object $response
     * @return bool
     */
    public function isValidResponse(object $response): bool
    {
        return property_exists($response, '_links');
    }
}
