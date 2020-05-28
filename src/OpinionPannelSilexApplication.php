<?php

namespace SquaredPoint;

use Silex\Application;
use Amqp\Silex\Provider\AmqpServiceProvider;
use Predis\Silex\ClientServiceProvider as RedisClientServiceProvider;
use Silex\Provider\MonologServiceProvider;

class OpinionPanelSilexApplication {

    private $silex;

    public function __construct()
    {
        $this->silex = new Application();
        $this->initMonolog();
        $this->initRabbitMQ();
        $this->initRedis();
    }

    public function getApp()
    {
        return $this->silex;
    }
    /**
     * Set up monolog to log to stderr
     */
    private function initMonolog()
    {
        $this->silex->register(new MonologServiceProvider(), [
            'monolog.logfile' => 'php://stderr',
            'monolog.level' => constant(
                'Monolog\\Logger::'.strtoupper(getenv('LOG_LEVEL')?:'NOTICE')
            ),
        ]);
    }

    private function initRabbitMQ()
    {
        $rabbitmq = parse_url(getenv('CLOUDAMQP_URL'));
        $this->silex->register(new AmqpServiceProvider(), [
            'amqp.connections' => [
                'default' => [
                    'host'    => $rabbitmq['host'],
                    'port'    => isset($rabbitmq['port'])? $rabbitmq['port']: 5672,
                    'username'=> $rabbitmq['user'],
                    'password'=> $rabbitmq['pass'],
                    'vhost'   => substr($rabbitmq['path'], 1) ?: '/',
                ],
            ],
        ]);
    }

    private function initRedis()
    {
        $this->silex->register(new RedisClientServiceProvider(),[
            'predis.parameters' => getenv('REDIS_URL'),
        ]);
    }
}

