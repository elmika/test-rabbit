<?php

require_once __DIR__.'/../vendor/autoload.php';

use \SquaredPoint\Exception\InvalidJson;
use \SquaredPoint\SilexApplicationBuilder;
use \SquaredPoint\OpinionPanelWorkerApplication;

$app = (new SilexApplicationBuilder())
    ->registerPurgomalum()
    ->getApp();

$opinionApp = new OpinionPanelWorkerApplication($app);
$opinionApp->logWorkerReady();

$callback = function($msg) use ($opinionApp) {
    $opinionApp->logNewTask($msg->body);
    try {
        // call the "censor" API and pass it the text to clean up
        $filteredOpinion = $opinionApp->filter($msg->body);

        $opinionApp->logCensoredMessage($filteredOpinion);

        // store in Redis
        $opinionApp->persistOpinion($filteredOpinion);

        // mark as delivered in RabbitMQ
        $opinionApp->deliverMessageAck($msg);
    } catch(InvalidJson $e) {
        $opinionApp->logFailedDecodeJSON($e->getInvalidJsonBody());
    } catch(Exception $e) {
        $opinionApp->logFailedAPICall($msg->body);
    }
};

$opinionApp->processMessages($callback);
$opinionApp->closeChannel();