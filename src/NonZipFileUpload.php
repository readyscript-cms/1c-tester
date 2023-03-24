<?php


namespace OneCTester;


use GuzzleHttp\RequestOptions;
use OneCTester\utils\Logger;
use OneCTester\utils\ResponseSplit;

class NonZipFileUpload extends Process
{
    public function doProcess(): void
    {
        Logger::log("Начинаем процесс выгрузки файлов (Non-ZIP)");
        $pathToData = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $this->parser->getParam('t') . DIRECTORY_SEPARATOR;
        $scanned_directory = array_values(array_diff(scandir($pathToData), ['.', '..']));
        foreach ($scanned_directory as $item) {
            Logger::log("Выгружаем файл " . $item);
            $handle = fopen($item, 'rb');
            $countbytes = 0;
            while (!feof($handle)) {
                $countbytes = min(filesize($pathToData . $item), $countbytes + $this->parser->getParam('file_limit'));
                Logger::log("Грузим $countbytes/" . filesize($pathToData . $item) . ' байт');

                $contents = fread($handle, $this->parser->getParam('file_limit'));
                $response = $this->client->post('?' . http_build_query([
                        'type' => $this->parser->getParam('t'),
                        'mode' => 'file',
                        'filename' => $item
                    ]), [
                    RequestOptions::BODY => $contents
                ]);

                if ($response->getBody()->getContents() != 'success') {
                    Logger::log("Ошибка загрузки файла. Прерываем операцию");
                    die();
                }

                Logger::log("Загрузили $countbytes/" . filesize($pathToData . $item) . ' байт');
            }

            Logger::log("Файл $item загружен");
            Logger::log("Подтверждение загрузки файла $item");

            while (true) {
                $response = $this->client->get('', [
                    'query' => [
                        'type' => $this->parser->getParam('t'),
                        'mode' => 'import',
                        'filename' => $item
                    ]
                ]);
                $contents = $response->getBody()->getContents();

                if ($contents != 'success' && $contents != 'progress') {
                    Logger::log('Ошибка импорта файла ' . $item . '. Прерываем операцию');
                    die();
                }

                if ($contents == 'progress') {
                    $split = ResponseSplit::split($contents);
                    Logger::log("Файл " . $item . ' обрабатывается, этап: ' . $split);
                } else {
                    Logger::log("Подтверждена загрузка файла $item");
                    break;
                }

                sleep(1);
            }
        }
    }
}