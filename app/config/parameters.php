<?php

$container->setParameter('database_driver', 'pdo_mysql');
$container->setParameter('database_host', 'localhost');
$container->setParameter('database_port', null);
$container->setParameter('database_name', 'lightning');
$container->setParameter('database_name_test', 'lightning_test');
$container->setParameter('database_user', 'root');
$container->setParameter('database_password', null);
$container->setParameter('database_path', null);

$container->setParameter('mailer_transport', 'smtp');
$container->setParameter('mailer_host', 'localhost');
$container->setParameter('mailer_user', null);
$container->setParameter('mailer_password', null);
$container->setParameter('locale', 'en');
$container->setParameter('secret', '579f85a6a7cb52df9101107ba82714f509');

$container->setParameter('urbanairship_key', '');
$container->setParameter('urbanairship_secret', '');
$container->setParameter('appstore_verify_url', 'https://sandbox.itunes.apple.com/verifyReceipt');
