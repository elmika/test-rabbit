<?php

namespace SquaredPoint;

use Silex\Application;
use Amqp\Silex\Provider\AmqpServiceProvider;
use Predis\Silex\ClientServiceProvider as RedisClientServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use SquaredPoint\SilexService\OpinionRepositoryServiceProvider;
use SquaredPoint\SilexService\PurgomalumServiceProvider;

class SilexApplicationBuilder {

    private $silex;

    public function __construct()
    {
        $this->silex = new Application();
        $this->registerMonolog()
             ->registerRabbitMQ(getenv('CLOUDAMQP_URL'))
             ->registerOpinionRepository();
    }

    public function getApp()
    {
        return $this->silex;
    }

    /**
     * Set up monolog to log to stderr
     * @return SilexApplicationBuilder
     */
    private function registerMonolog() : SilexApplicationBuilder
    {
        $this->silex->register(new MonologServiceProvider(), [
            'monolog.logfile' => 'php://stderr',
            'monolog.level' => constant(
                'Monolog\\Logger::'.strtoupper(getenv('LOG_LEVEL')?:'NOTICE')
            ),
        ]);
        return $this;
    }

    /**
     * @param string $config
     * @return SilexApplicationBuilder
     */
    private function registerRabbitMQ(string $config) : SilexApplicationBuilder
    {
        $rabbitmq = parse_url($config);
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
        return $this;
    }

    private function registerOpinionRepository() : SilexApplicationBuilder
    {
        $this->silex->register(new OpinionRepositoryServiceProvider());
        return $this;
    }

    /**
     * @return SilexApplicationBuilder
     */
    public function registerPurgomalum() : SilexApplicationBuilder
    {
        $this->silex->register(new PurgomalumServiceProvider());
        return $this;
    }

    public function registerForm() : SilexApplicationBuilder
    {
        $this->silex->register(new FormServiceProvider());
        return $this;
    }

    public function registerTwig() : SilexApplicationBuilder
    {
        $this->silex->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__.'/..',
            'twig.form.templates' => ['bootstrap_3_layout.html.twig'],
        ]);
        return $this;
    }

    public function registerTranslation() : SilexApplicationBuilder
    {
        $this->silex->register(new TranslationServiceProvider(), [
            'translator.messages' => []
        ]);
        return $this;
    }
}

