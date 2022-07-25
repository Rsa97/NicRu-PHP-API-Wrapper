<?php

require_once 'vendor/autoload.php';
require_once 'secure/nic.php';

// use \Rsa97\AcmeV2;
use \Rsa97\NicRu;

// $acmev2 = new AcmeV2\Client('https://acme-staging-v02.api.letsencrypt.org/directory');
// $ids = [
//     new AcmeV2\Identifier(AcmeV2\IdentifierType::DNS, 'sodrk.ru')
// ];
// $acmev2->newOrder($ids);
// $auths = $acmev2->getAuthorizations();
// foreach ($auths as $auth) {
//     $acmev2->authorize($auth);
// }
// var_dump($auths);

// $dns = new NicRu\Client(
//     NICCredentials::NIC_LOGIN,
//     NICCredentials::NIC_PASSWORD,
//     NICCredentials::API_LOGIN,
//     NICCredentials::API_PASSWORD,
//     [new NicRu\Scope([NicRu\ScopeMethod::GET, NicRu\ScopeMethod::POST, NicRu\ScopeMethod::PUT, NicRu\ScopeMethod::DELETE])]
// );
// $srv = $dns->getService('prst-sodrk-ru');
// $zone = $srv->getZone('sodrk.ru');
var_dump($zone);