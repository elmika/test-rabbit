<?php


namespace SquaredPoint\SilexService;


use Predis\Client;
use Silex\Application;
use Silex\ServiceProviderInterface;
use SquaredPoint\OpinionRedisRepository;
use SquaredPoint\OpinionRepository;

class OpinionRepositoryServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */

    public function boot(Application $app)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['opinions'] = $app->share(function () use ($app) {
            $redisClient = new Client(getenv('REDIS_URL'));
            return new OpinionRedisRepository($redisClient);
        });
    }
}