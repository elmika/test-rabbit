<?php

namespace SquaredPoint;

use SquaredPoint\Exception\InvalidJson;

final class OpinionPanelWorkerApplication extends OpinionPanelBaseApplication
{
    /* ********************
     *  Redis Service
     ******************** */
    public function persistOpinion($filteredOpinion) : void
    {
        $this->silex['opinions']->addOpinion($filteredOpinion);
    }

    /* ********************
     *  Purgomalum Service
     ******************** */
    public function filter(string $msgBody) : string
    {
        return $this->silex['purgomalum']->filter($msgBody);
    }

    /* ********************
     *  Monolog Service
     ******************** */
    public function logWorkerReady()
    {
        $this->silex['monolog']->info('Worker ready for messages.');
    }

    public function logNewTask(string $messageBody) : void
    {
        $this->silex['monolog']->debug('New task received for censoring message: ' . $messageBody);
    }

    public function logCensoredMessage(string $filteredOpinion)
    {
        $this->silex['monolog']->debug(
            'Censored message result is: '
            . $filteredOpinion
        );
    }

    public function logFailedDecodeJSON(InvalidJson $e) : void
    {
        $this->silex['monolog']->warning(
            'Failed to decode JSON, will retry later - problematic response is: ' .
            $e->getInvalidJsonBody()
        );
    }

    public function logFailedAPICall($messageBody) : void
    {
        $this->silex['monolog']->warning(
            'Failed to call API, will retry later - problematic message is: '
            .$messageBody
        );
    }

    /* ********************
     *  RabbitMQ Service
     ******************** */

    /**
     * Retrieve messages from the queue, process them.
     *
     * @param \Closure $callback
     */
    public function processMessages(\Closure $callback)
    {
        $this->getChannel()->basic_qos(null, 1, null);
        $this->getChannel()->basic_consume('task_queue', '', false, false, false, false, $callback);

        // loop over incoming messages
        while(count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait();
        }
    }

    public function deliverMessageAck($msg)
    {
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }
}