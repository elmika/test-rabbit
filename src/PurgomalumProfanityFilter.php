<?php


namespace SquaredPoint;


use GuzzleHttp\Client;

class PurgomalumProfanityFilter
{
    private $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = null;
    }

    private function createGuzzleClient() : void
    {
        $this->guzzleClient = new Client([
            'base_uri' => 'https://www.purgomalum.com/service/',
            'timeout' => 5,
            'headers' => [
                'Accept' => 'application/json'
            ],
        ]);
    }

    private function hasGuzzleClient()
    {
        return ! is_null($this->guzzleClient);
    }

    /**
     * @param string $text
     * @return string
     * @throws Exception\InvalidJson
     */
    public function filter(string $text) : string
    {
        if( ! $this->hasGuzzleClient()){
            $this->createGuzzleClient();
        }

        $response = $this->guzzleClient->get('json', ['query' => ['text' => $text]]);
        $body = json_decode($response->getBody());
        if(!$body){
            throw new Exception\InvalidJson($response->getBody());
        }
        return $body->result;
    }
}