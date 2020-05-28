<?php

namespace SquaredPoint\SilexService;

use Silex\Application;
use Silex\ServiceProviderInterface;
use SquaredPoint\PurgomalumProfanityFilter;

/**
 * Class PurgomalumServiceProvider
 * @author Mika <1506612+elmika@users.noreply.github.com>
 */
class PurgomalumServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array
     */
    private $configuration = array();

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
        $app['purgomalum'] = $app->share(function () use ($app) {

            return new PurgomalumProfanityFilter();
        });
    }
}
