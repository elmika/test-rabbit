<?php

require_once __DIR__.'/../vendor/autoload.php';

use \SquaredPoint\Exception\InvalidJson;
use \SquaredPoint\SilexApplicationBuilder;

$app = (new SilexApplicationBuilder())
    ->registerPurgomalum()
    ->getApp();

$connection = $app['amqp']['default'];
/** @var PhpAmqpLib\Channel\AMQPChannel $channel */
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

$app['monolog']->info('Worker ready for messages.');

$callback = function($msg) use ($app) {
    $app['monolog']->debug('New task received for censoring message: ' . $msg->body);
    try {
        // call the "censor" API and pass it the text to clean up
        $filteredOpinion = $app['purgomalum']->filter($msg->body);

        $app['monolog']->debug('Censored message result is: ' . $filteredOpinion);
        // store in Redis
        $app['opinions']->addOpinion($filteredOpinion);
        // mark as delivered in RabbitMQ
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    } catch(InvalidJson $e) {
        $app['monolog']->warning('Failed to decode JSON, will retry later - problematic response is: ' . $e->getInvalidJsonBody());
    } catch(Exception $e) {
        $app['monolog']->warning('Failed to call API, will retry later - problematic message is: '.$msg->body);
    }
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

// loop over incoming messages
while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();