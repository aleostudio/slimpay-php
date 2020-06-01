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

## TODO

### Authentication
- [x] Authentication flow

### Iframe
- [ ] Create orders
- [ ] Iframe redirect
- [ ] Get token

### Testing
- [ ] Unit testing

