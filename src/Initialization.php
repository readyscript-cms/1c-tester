<?php


namespace OneCTester;

use OneCTester\utils\Logger;
use OneCTester\utils\ResponseSplit;

class Initialization extends Process
{
    public function doProcess(): void
    {
        Logger::log('Инициализация');

        $response = $this->client->request('GET', '', [
            'query' => [
                'type' => $this->parser->getParam('t'),
                'mode' => 'init'
            ]
        ]);
        $split = ResponseSplit::split($response->getBody()->getContents());
        if ($split[0] == 'failure') {
            Logger::log('Ошибка инициализации!');
            die();
        }

        $zipValue = explode('=', $split[0]);
        $fileLimitValue = explode('=', $split[1]);

        $this->parser->setParam($zipValue[0], $zipValue[1] == 'yes');
        $this->parser->setParam($fileLimitValue[0], $fileLimitValue[1]);

        Logger::log('Инициализация успешна');
    }
}