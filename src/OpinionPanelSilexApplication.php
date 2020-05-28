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

class OpinionPanelSilexApplication {

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
     * @return OpinionPanelSilexApplication
     */
    private function registerMonolog() : OpinionPanelSilexApplication
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
     * @return OpinionPanelSilexApplication
     */
    private function registerRabbitMQ(string $config) : OpinionPanelSilexApplication
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

    private function registerOpinionRepository() : OpinionPanelSilexApplication
    {
        $this->silex->register(new OpinionRepositoryServiceProvider());
        return $this;
    }

    /**
     * @return OpinionPanelSilexApplication
     */
    public function registerPurgomalum() : OpinionPanelSilexApplication
    {
        $this->silex->register(new PurgomalumServiceProvider());
        return $this;
    }

    public function registerForm() : OpinionPanelSilexApplication
    {
        $this->silex->register(new FormServiceProvider());
        return $this;
    }

    public function registerTwig() : OpinionPanelSilexApplication
    {
        $this->silex->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__.'/..',
            'twig.form.templates' => ['bootstrap_3_layout.html.twig'],
        ]);
        return $this;
    }

    public function registerTranslation() : OpinionPanelSilexApplication
    {
        $this->silex->register(new TranslationServiceProvider(), [
            'translator.messages' => []
        ]);
        return $this;
    }
}

