<?php

require_once __DIR__.'/../vendor/autoload.php';

use SquaredPoint\OpinionPanelSilexApplication;
use PhpAmqpLib\Message\AMQPMessage;

$app = (new OpinionPanelSilexApplication())
    ->registerForm()
    ->registerTranslation()
    ->registerTwig()
    ->getApp();

$app->match('/', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
   $opinions = $app['opinions']->readOpinions();

   /** @var $form \Symfony\Component\Form\Form */
   $form = $app['form.factory']->createBuilder('form')
       ->add('opinion', 'textarea', [
           'label' => 'Your opinion',
           'attr' => ['rows' => count($opinions)*2],
       ])
       ->getForm();
   $form->handleRequest($request);
   $submitted = false;
   if($form->isValid()){
       $data = $form->getData();

       $connection = $app['amqp']['default'];
       /** @var $channel \PhpAmqpLib\Channel\AMQPChannel */
       $channel = $connection->channel();
       $channel->queue_declare('task_queue', false, true, false, false);

       foreach(explode( "\n", $data['opinion']) as $newOpinion) {
           $msg = new AMQPMessage($newOpinion, ['delivery_mode' => 2]);
           $channel->basic_publish($msg, '', 'task_queue');
       }

       $channel->close();
       $connection->close();

       $submitted = true;
   }

   return $app['twig']->render('index.twig', [
       'form' => $form->createView(),
       'submitted' => $submitted,
       'opinions' => $opinions
   ]);
});

$app->run();