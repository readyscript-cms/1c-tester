<?php


namespace OneCTester;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use OneCTester\utils\Logger;
use OneCTester\utils\ResponseSplit;

/**
 * Класс отвечает за авторизацию
 * Class Auth
 * @package OneCTester
 */
class Auth extends Process
{
    public function doProcess(): void
    {
        Logger::log('Попытка авторизации по указанным данным');

        try {
            $response = $this->client->request('GET', '', [
                'query' => [
                    'type' => $this->parser->getParam('t'),
                    'mode' => 'checkauth'
                ]
            ]);

            $contents = $response->getBody()->getContents();
            $explode = ResponseSplit::split($contents);
            if ($explode[0] == 'failure') {
                Logger::log('Ошибка авторизации. Выходим из программы');
                die();
            }

            $cookieName = $explode[1];
            $cookieValue = $explode[2];

            $this->client = new Client([
                'base_uri' => $this->parser->getParam('s'),
                'timeout' => 2.0,
                'headers' => [
                    'Cookie' => $cookieName.'='.$cookieValue
                ]
            ]);

            Logger::log('Авторизация успешна!');
        } catch (ClientException $exception) {
            echo $exception->getMessage();
        }
    }
}