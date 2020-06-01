# SlimPay Iframe

A simple PHP package to integrate the SlimPay Iframe on your application. This package will be officially published 
in the **LunaLabs** repository.

<br />

## Installation

If you have cloned this package somewhere in your disk, simply add into your **composer.json** these lines, customizing 
the path of the package. Then, launch a **composer install**
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

// Slimpay credentials.
$slimpayConfig = [
    'creditor'   => 'xxxxxxxxxxx',
    'appId'      => 'xxxxxxxxxxxx',
    'appSecret'  => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
    'baseUri'    => 'https://api.preprod.slimpay.com',
    'profileUri' => 'https://api.slimpay.net',
    'apiVersion' => 'v1'
];

// Instance.
$slimpay = new SlimPayIframe($slimpayConfig);

...
```

<br />

## Server notification

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

### Iframe
- [ ] Create orders
- [ ] Iframe redirect
- [ ] Get token

### Testing
- [ ] Unit testing

