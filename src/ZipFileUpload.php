<?php


namespace OneCTester;


use GuzzleHttp\RequestOptions;
use OneCTester\utils\Logger;
use OneCTester\utils\ResponseSplit;

class ZipFileUpload extends Process
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doProcess(): void
    {
        Logger::log("Начинаем процесс выгрузки файлов (ZIP)");
        Logger::log("Формируем zip-архив из data");

        $zipPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $this->parser->getParam('t') . DIRECTORY_SEPARATOR . 'data.zip';
        $zipArchive = new \ZipArchive();
        $zipArchive->open($zipPath, \ZipArchive::CREATE);

        $scanned_directory = array_values(array_diff(scandir(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $this->parser->getParam('t')), array('..', '.')));
        foreach ($scanned_directory as $file) {
            $zipArchive->addFile(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $this->parser->getParam('t') . DIRECTORY_SEPARATOR . $file, $file);
        }

        $zipArchive->close();

        Logger::log("Закончили формировать архив.");
        Logger::log("Начинаем побайтовую отправку архива");
        $handle = fopen($zipPath, 'rb');

        $countbytes = 0;
        Logger::log("$countbytes/" . filesize($zipPath) . ' байт');

        while (!feof($handle)) {
            $countbytes = min(filesize($zipPath), $countbytes + $this->parser->getParam('file_limit'));
            Logger::log("Грузим $countbytes/" . filesize($zipPath) . ' байт');

            $contents = fread($handle, $this->parser->getParam('file_limit'));
            $response = $this->client->post('?' . http_build_query([
                    'type' => $this->parser->getParam('t'),
                    'mode' => 'file',
                    'filename' => 'data.zip'
            ]), [
                RequestOptions::BODY => $contents
            ]);

            if ($response->getBody()->getContents() != 'success') {
                Logger::log("Ошибка загрузки файла. Прерываем операцию");
                die();
            }

            Logger::log("Загрузили $countbytes/" . filesize($zipPath) . ' байт');
        }

        fclose($handle);

        unlink($zipPath);

        Logger::log("Начинаем обработку импорта");

        $uploadMap = [];

        while (true) {
            $countInProcess = 0;
            foreach ($scanned_directory as $item) {
                if (isset($uploadMap[$item]) && $uploadMap[$item]) {
                    continue;
                }

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
                    $countInProcess++;
                } else {
                    Logger::log("Файл "  . $item . ' успешно загружен');
                    $uploadMap[$item] = true; //значит файл загружен
                }
            }

            if ($countInProcess == 0) {
                break;
            }

            sleep(1);
        }

        Logger::log("Программа завершила свою работу. Все файлы загружены");
    }
}