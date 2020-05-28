<?php

require_once __DIR__.'/../vendor/autoload.php';

use SquaredPoint\SilexApplicationBuilder;
use \SquaredPoint\OpinionPanelWebApplication;

$app = (new SilexApplicationBuilder())
    ->registerForm()
    ->registerTranslation()
    ->registerTwig()
    ->getApp();

$opinionApp = new OpinionPanelWebApplication($app);

$app->match('/', function (Symfony\Component\HttpFoundation\Request $request) use ($opinionApp) {
   $form = $opinionApp->getOpinionForm();
   $form->handleRequest($request);
   $submitted = false;
   if($form->isValid()){
       $data = $form->getData();
       foreach(explode( "\n", $data['opinion']) as $newOpinion) {
           $opinionApp->publishToQueue($newOpinion);
       }
       $opinionApp->closeChannel();
       $submitted = true;
   }

    $opinions = $opinionApp->readOpinions();
    return $opinionApp->render($submitted, $opinions);
});

$app->run();