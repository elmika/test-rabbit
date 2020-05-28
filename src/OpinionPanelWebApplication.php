<?php


namespace SquaredPoint;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Predis\Connection\ConnectionInterface;
use Silex\Application;
use Symfony\Component\Form\Form;

class OpinionPanelWebApplication
{
    /**
     * @var Application
     */
    private $silex;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var Form
     */
    private $form;

    /**
     * OpinionPanelWebApplication constructor.
     * @param Application $silex
     */
    public function __construct(Application $silex)
    {
        $this->silex = $silex;
        $this->channel = null;
    }

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

    private function initChannel() : void
    {
        /**
         * @var ConnectionInterface
         */
        $connection = $this->silex['amqp']['default'];
        $connection->channel()->queue_declare('task_queue', false, true, false, false);

        $this->channel = $connection->channel();
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel()
    {
        if( null === $this->channel ) {
            $this->initChannel();
        }
        return $this->channel;
    }

    /**
     * @param string $opinion
     */
    public function publishToQueue(string $opinion) : void
    {
        $message = new AMQPMessage($opinion, ['delivery_mode' => 2]);
        $this->getChannel()->basic_publish($message, '', 'task_queue');
    }

    public function closeChannel() : void
    {
        $this->channel->close();
        $this->channel = null;

        /**
         * @var ConnectionInterface
         */
        $connection = $this->silex['amqp']['default'];
        $connection->close();
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