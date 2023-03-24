<?php

use GuzzleHttp\Client;
use OneCTester\NonZipFileUpload;
use OneCTester\ParamsRepository;
use OneCTester\Auth;
use OneCTester\utils\Logger;
use OneCTester\ZipFileUpload;
use OneCTester\Initialization;

require __DIR__ . '/vendor/autoload.php';

$argvParser = new ParamsRepository($argv);
$argvParser->parse();

if ($argvParser->getParam('all') == true) {
    $type = ['catalog', 'sale'];
} else {
    $type = [$argvParser->getParam('t')];
}

foreach ($type as $item) {
    Logger::log("Выгрузка " . $item);

    $argvParser->setParam('t', $item);

    $client = new Client([
        'base_uri' => $argvParser->getParam('s'),
        'timeout' => 2.0,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($argvParser->getParam('l') . ':' . $argvParser->getParam('p'))
        ]
    ]);

    try {
        $auth = new Auth($client, $argvParser);
        $auth->doProcess();

        $client = $auth->getClient();

        $initialization = new Initialization($client, $argvParser);
        $initialization->doProcess();

        if ($argvParser->getParam('zip')) {
            $fileUpload = new ZipFileUpload($client, $argvParser);
        } else {
            $fileUpload = new NonZipFileUpload($client, $argvParser);
        }

        $fileUpload->doProcess();
    } catch (\GuzzleHttp\Exception\GuzzleException $exception) {
        echo 'Произошла ошибка Guzzle. Сообщение: ' . $exception->getMessage();
    }
}