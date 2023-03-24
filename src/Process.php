<?php


namespace OneCTester;


use GuzzleHttp\Client;

abstract class Process
{
    /**
     * Клиент - первично задается из стартового скрипта, но он также может меняться внутри каждого процесса
     * @var Client
     */
    protected Client $client;

    /**
     * Общий доступ к параметрами переданным в программу через командную строку
     * @var ParamsRepository
     */
    protected ParamsRepository $parser;

    /**
     * Конструктор каждого процесса происходящего в эмуляторе
     * @param Client $client
     * @param ParamsRepository $parser
     */
    public function __construct(Client $client, ParamsRepository $parser)
    {
        $this->client = $client;
        $this->parser = $parser;
    }

    /**
     * Т.к внутри процесса клиент может меняться перед вызовом следующего процесса нужно вызывать этот метод
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    abstract public function doProcess(): void;
}