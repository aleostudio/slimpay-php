<?php
/**
 * This file is part of the SlimPay Iframe package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LunaLabs\SlimPayIframe\Test;

use LunaLabs\SlimPayIframe\SlimPayIframe;
use LunaLabs\SlimPayIframe\Exceptions\SlimPayIframeException;
use LunaLabs\SlimPayIframe\Http\Client;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;


class SlimPayTest extends TestCase
{
    protected $config   = [];
    protected $sepaData = [];
    protected $ccData   = [];


    public function setUp(): void
    {
        $this->config = [
            'creditor'   => 'xxxxxxxxxxx',
            'appId'      => 'xxxxxxxxxxxx',
            'appSecret'  => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            'baseUri'    => 'https://api.preprod.slimpay.com',
            'profileUri' => 'https://api.slimpay.net',
            'apiVersion' => 'v1',
            'mode'       => 'iframe' // iframe | redirect
        ];

        $this->sepaData = [
            'started'    => true,
            'creditor'   => ['reference' => $this->config['creditor']],
            'subscriber' => ['reference' => 'unitTestUserId1'],
            'items'      => [[
                'type' => 'signMandate',
                'mandate' => [
                    'signatory' => [
                        'givenName'      => 'TestFirstname',
                        'familyName'     => 'TestLastname',
                        'email'          => 'testemail@domain.com',
                        'telephone'      => '+393470000000',
                        'billingAddress' => [
                            'street1'    => 'Some Address 123',
                            'postalCode' => '01234',
                            'city'       => 'Cityname',
                            'country'    => 'IT'
                        ],
                    ]
                ]
            ]],
            'failureUrl' => 'https://domain.com/failure_url.php',
            'successUrl' => 'https://domain.com/success_url.php',
            'cancelUrl'  => 'https://domain.com/cancel_url.php',
        ];

        $this->ccData = [
            'started'        => true,
            'locale'         => 'it',
            'paymentScheme'  => 'CARD',
            'creditor'       => ['reference' => $this->config['creditor']],
            'items'          => [['type' => 'cardAlias']],
            'subscriber'     => [
                'reference'  => 'unitTestUserId1',
                'givenName'  => 'TestFirstname',
                'familyName' => 'TestLastname',
                'email'      => 'testemail@domain.com',
                'telephone'  => '+393470000000',
            ],
            'failureUrl' => 'https://domain.com/failure_url.php',
            'successUrl' => 'https://domain.com/success_url.php',
            'cancelUrl'  => 'https://domain.com/cancel_url.php',
        ];
    }


    public function testSlimPayInstanceWithoutConfig()
    {
        $this->expectException(SlimPayIframeException::class);
        $slimpay = new SlimPayIframe();
    }


    public function testConfigKeys()
    {
        $this->assertArrayHasKey('creditor',   $this->config, "The config does not contains the 'creditor'");
        $this->assertArrayHasKey('appId',      $this->config, "The config does not contains the 'appId'");
        $this->assertArrayHasKey('appSecret',  $this->config, "The config does not contains the 'appSecret'");
        $this->assertArrayHasKey('baseUri',    $this->config, "The config does not contains the 'baseUri'");
        $this->assertArrayHasKey('profileUri', $this->config, "The config does not contains the 'profileUri'");
        $this->assertArrayHasKey('apiVersion', $this->config, "The config does not contains the 'apiVersion'");
        $this->assertArrayHasKey('mode',       $this->config, "The config does not contains the 'mode'");
    }


    public function testConfigBaseUri()
    {
        $urlRegex = "/^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
        $result = (bool) preg_match($urlRegex, $this->config['baseUri']);
        $this->assertEquals(true, $result, "The config 'baseUri' is not a valid URL");
    }


    public function testConfigProfileUri()
    {
        $urlRegex = "/^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
        $result = (bool) preg_match($urlRegex, $this->config['profileUri']);
        $this->assertEquals(true, $result, "The config 'profileUri' is not a valid URL");
    }


    public function testGetAccessToken()
    {
        $client = new Client($this->config);
        $token = $client->getToken();
        $this->assertArrayHasKey('access_token', (array) $token, "The retrieved token object is invalid'");
    }


    public function testGetAccessTokenWithWrongCredentials()
    {
        $wrongConfig = $this->config;
        $wrongConfig['appSecret'] = 'wrongAppSecret';
        $client = new Client($wrongConfig);
        $this->expectException(SlimPayIframeException::class);
        $token = $client->getToken();
    }


    public function testGetResource()
    {
        $slimpay = new SlimPayIframe($this->config);
        $resourceUrl = $this->config['baseUri'].'/creditors/'.$this->config['creditor'];
        $response = $slimpay->getResource($resourceUrl);
        $this->assertInstanceOf('StdClass', $response);
        $this->assertArrayHasKey('_links', (array) $response);
        $this->assertInstanceOf('StdClass', $response->_links);
    }


    public function testCheckoutWithWrongData()
    {
        $slimpay = new SlimPayIframe($this->config);
        $data = ['wrongCheckoutData'];
        $this->expectException(SlimPayIframeException::class);
        $response = $slimpay->checkout($data);
    }


    public function testCheckoutSEPA()
    {
        $slimpay  = new SlimPayIframe($this->config);
        $response = $slimpay->checkout($this->sepaData);
        $this->assertTrue($slimpay->isValidResponse($response), 'The checkout response is not valid.');
        $this->assertArrayHasKey('paymentScheme', (array) $response);
        $this->assertEquals('SEPA.DIRECT_DEBIT.CORE', $response->paymentScheme, 'The payment scheme is not a "SEPA.DIRECT_DEBIT.CORE".');
    }


    public function testCheckoutCreditCard()
    {
        $slimpay  = new SlimPayIframe($this->config);
        $response = $slimpay->checkout($this->ccData);
        $this->assertTrue($slimpay->isValidResponse($response), 'The checkout response is not valid.');
        $this->assertArrayHasKey('paymentScheme', (array) $response);
        $this->assertEquals('CARD', $response->paymentScheme, 'The payment scheme is not a "CARD".');
    }
}