# SlimPay Iframe

A simple PHP package to integrate the SlimPay Iframe on your application. This package will be officially published 
in the **LunaLabs** repository.

<br />

## Installation
If you want to try the package, simply clone it and install its dependencies with:
```bash
composer install
```
If you want to simulate the package inside your Laravel app, simply clone this repo, copy it into your vendor dir and add into your **composer.json** these lines and then launch a **composer install**
```
...
"require": {
    "lunalabs/slimpay-iframe": "*"
},
"repositories": [
    { "type": "path", "url": "vendor/lunalabs/slimpay-iframe" }
]
```

Alternatively (but not available yet), you can directly install the package with:
```bash
composer require lunalabs/slimpay-iframe
```
<br />

## Test the package
Create a simple PHP file with these lines:
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use LunaLabs\SlimPayIframe\SlimPayIframe;
use LunaLabs\SlimPayIframe\SlimPayNotification;

// Slimpay credentials.
$slimpayConfig = [
    'creditor'   => 'xxxxxxxxxxx',
    'appId'      => 'xxxxxxxxxxxx',
    'appSecret'  => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
    'baseUri'    => 'https://api.preprod.slimpay.com',
    'profileUri' => 'https://api.slimpay.net',
    'apiVersion' => 'v1',
    'mode'       => 'iframe' // iframe|redirect
];

// Instance.
$slimpay = new SlimPayIframe($slimpayConfig);
```

<br />

# Credit card checkout with SlimPay Iframe or redirection
To create the SlimPay Iframe for Credit Cards you have to init an array like this one below and call the **checkout** method.
Customize the data and the return urls (failureUrl, successUrl and cancelUrl) with yours.
```php
$data = [
    'started'        => true,
    'locale'         => 'it',
    'paymentScheme'  => 'CARD',
    'creditor'       => ['reference' => 'xxxxxxxxxxx'],
    'items'          => [['type' => 'cardAlias']],
    'subscriber'     => [
        'reference'  => 'yourUniqueUserId',
        'givenName'  => 'John',
        'familyName' => 'Doe',
        'email'      => 'john.doe@domain.com',
        'telephone'  => '+393470000000',
    ],
    'failureUrl' => 'http://yourdomain.com/failure.php',
    'successUrl' => 'http://yourdomain.com/success.php',
    'cancelUrl'  => 'http://yourdomain.com/cancel.php'
];

$response = $slimpay->checkout($data);

// The checkout flow returns the order status. If it is 'open.running' we can go on.
if ($response->state == 'open.running') {
    $resourceLinks = [
        'userApproval'     => 'https://api.slimpay.net/alps#user-approval',
        'extendedApproval' => 'https://api.slimpay.net/alps#extended-user-approval',
    ];

    switch ($slimpayConfig['mode']) {

        case 'redirect':
            $link = $response->_links->{$resourceLinks['userApproval']}->href;
            header('Location: ' . $link);
            break;

        case 'iframe':
            $link    = $response->_links->{$resourceLinks['extendedApproval']}->href;
            $link    = str_replace('{?mode}', '', $link);
            $encoded = $slimpay->getResource($link, ['mode' => 'iframeembedded']);
            $html    = base64_decode($encoded->content);
            echo $html;
            break;
    }
}
```
If the response has the **user approval link** you will be redirected to the **SlimPay checkout page**.
Once you have filled the checkout form, a detailed response will be sent to the **Server notification URI** set in your 
SlimPay application, containing the **credit card ID** and **reference ID** to be sent to your Payment Gateway to finish the flow.
[Server Notification Reference](https://support.slimpay.com/hc/en-us/articles/360001565338-URLs-Management)

<br />

## Server notification URL

It is possible to handle the **SlimPay server notification**.
To instantiate the notification handler, write these lines:
```php
$notification = new SlimPayNotification();
$response     = $notification->getResponse();
```

<br />



If you want to **log the notification response**, you can inject you custom logger as parameter.
Pay attention that your logger must have a "**write()**" method inside, as shown in this simple example below.
```php
class Log
{
    public function write($input)
    {
        $path = "./slimpay_notifications.log";
        error_log(json_encode($input), 3, $path);
    }
}

$customLog    = new Log;
$notification = new SlimPayNotification($customLog);
$response     = $notification->getResponse();
```

<br />

## TODO

### Authentication
- [x] Authentication flow

### Credit card checkout
- [x] Create order
- [x] Checkout redirect
- [x] Embedded Iframe
- [x] Get notification response to retrieve the card and reference IDs

### SEPA checkout
- [ ] Create order
- [ ] Iframe redirect
- [ ] Get notification response to retrieve the RUM

### Testing
- [ ] Unit testing

