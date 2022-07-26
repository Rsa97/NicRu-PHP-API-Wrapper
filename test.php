<?php

require_once 'vendor/autoload.php';
require_once 'secure/nic.php';

use \Rsa97\NicRu;

$dns = new NicRu\Client(
    NICCredentials::NIC_LOGIN,
    NICCredentials::NIC_PASSWORD,
    NICCredentials::API_LOGIN,
    NICCredentials::API_PASSWORD,
    [new NicRu\Scope([NicRu\ScopeMethod::GET, NicRu\ScopeMethod::POST, NicRu\ScopeMethod::PUT, NicRu\ScopeMethod::DELETE])]
);
$srv = $dns->getService('prst-sodrk-ru');
$zone = $srv->getZone('sodrk.ru');
// var_dump($zone);
$rrs = $zone->getResourceRecords(name: '@');
var_dump($rrs);
// $zone->deleteResourceRecords(type: null, name: '_acme-challenge');
// $zone->commit();
// $rrs = $zone->getResourceRecords(type: NicRu\ResourceRecordType::TXT);
// var_dump($rrs);
