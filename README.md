Example
=============

```php

<?php
require_once('./library/Api.php');

$client = new Api([
    'public_key' => 'your public_key',
    'private_key' => 'your private_key'
]);


try {
    print_r($client->get('/api/v1/market'));
    //print_r($client->get('/api/v1/ticker?market=btc_usd'));
    //print_r($client->post('/api/v1/trading/balances'));
    //print_r($client->post('/api/v1/trading/order/create', ['market' => 'btc_usd', 'type' => 'sell', 'price' => 1.03, 'amount' => 0.0023]));
} catch (Exception $e) {
    print_r($e->getMessage());
}
```

API documentation
=============

[API](https://bitebtc.com/api/)