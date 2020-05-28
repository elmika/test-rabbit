<?php


namespace SquaredPoint;


use Predis\Client;

class OpinionRedisRepository implements OpinionRepository
{

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function addOpinion(string $opinion) : void
    {
        $this->client->lpush('opinions', [$opinion]);
    }

    public function readOpinions() : array
    {
        return $this->client->lrange('opinions', 0, 10);
    }
}