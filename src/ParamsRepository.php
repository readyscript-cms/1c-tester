<?php

namespace OneCTester;

/**
 * Класс парсит данные которые передаются в $argv, также сюда в params сохраняется локальная информация полученная в запросах
 * Class ArgvParser
 * @package OneCTester
 */
class ParamsRepository
{
    /**
     * Параметры, массив наполняется после parse()
     * Параметры вида key => value, например php 1cexchange.php -s siteurl.ru будет спаршено как s => siteurl.ru
     * @var array
     */
    private array $params = [];

    /**
     * Массив $argv
     * @var array
     */
    private array $argv = [];

    public function __construct(array $argv)
    {
        $this->argv = $argv;
    }

    public function parse()
    {
        array_shift($this->argv); //удаляем 0 элемент из argv (название скрипта)
        $currentParam = null;
        foreach ($this->argv as $param) {
            if ($currentParam == null) {
                if (substr($param, 0, 2) == '--') { //детектим является ли строка ключем типа true
                    $currentParam = ltrim($param, '--');
                    $this->params[$currentParam] = true;
                } else if (substr($param, 0, 1) == '-') { //детектим является ли строка ключем параметра
                    $currentParam = ltrim($param, '-');
                }
            } else {
                $this->params[$currentParam] = $param;
                $currentParam = null;
            }
        }
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getParam(string $key): ?string
    {
        return $this->params[$key] ?? null;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function setParam(string $key, $value): void
    {
        $this->params[$key] = $value;
    }
}