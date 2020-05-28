<?php


namespace SquaredPoint;


use PhpAmqpLib\Message\AMQPMessage;
use Predis\Connection\ConnectionInterface;
use Silex\Application;
use Symfony\Component\Form\Form;

final class OpinionPanelWebApplication extends OpinionPanelBaseApplication
{
    /**
     * @var Form
     */
    private $form;

    public function getOpinionForm()
    {
        if( null === $this->form)
        {
            $this->initOpinionForm();
        }
        return $this->form;
    }

    private function initOpinionForm() : void
    {
        $formFactory = $this->silex['form.factory'];
        $this->form = $formFactory->createBuilder('form')
            ->add('opinion', 'textarea', [
                'label' => 'Your opinion',
                'attr' => ['rows' => 6],
            ])
            ->getForm();
    }

    /**
     * @param string $opinion
     */
    public function publishToQueue(string $opinion) : void
    {
        $message = new AMQPMessage($opinion, ['delivery_mode' => 2]);
        $this->getChannel()->basic_publish($message, '', 'task_queue');
    }

    public function readOpinions() : array
    {
        return $this->silex['opinions']->readOpinions();
    }

    public function render(bool $submitted, array $opinions)
    {
        return $this->silex['twig']->render('index.twig', [
            'form' => $this->getOpinionForm()->createView(),
            'submitted' => $submitted,
            'opinions' => $opinions
        ]);
    }
}