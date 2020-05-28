<?php


namespace SquaredPoint;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use Silex\Application;

class OpinionPanelBaseApplication
{
    /**
     * @var Application
     */
    protected $silex;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * OpinionPanelWebApplication constructor.
     * @param Application $silex
     */
    public function __construct(Application $silex)
    {
        $this->silex = $silex;
        $this->channel = null;
    }

    private function initChannel() : void
    {
        /**
         * @var AbstractConnection
         */
        $connection = $this->silex['amqp']['default'];
        $connection->channel()->queue_declare('task_queue', false, true, false, false);

        $this->channel = $connection->channel();
    }

    /**
     * @return AMQPChannel
     */
    protected function getChannel()
    {
        if( null === $this->channel ) {
            $this->initChannel();
        }
        return $this->channel;
    }

    public function closeChannel() : void
    {
        $this->channel->close();
        $this->channel = null;

        /**
         * @var AbstractConnection
         */
        $connection = $this->silex['amqp']['default'];
        $connection->close();
    }
}