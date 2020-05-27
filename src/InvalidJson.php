<?php


namespace SquaredPoint;

class InvalidJson extends \Exception
{
    private $invalidJsonBody;

    public function __construct($invalidJsonBody){
        parent::__construct();
        $this->invalidJsonBody = $invalidJsonBody;
    }

    public function getInvalidJsonBody()
    {
        return $this->invalidJsonBody;
    }
}