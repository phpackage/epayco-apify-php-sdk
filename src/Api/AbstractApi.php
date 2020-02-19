<?php

namespace Phpackage\Epayco\Apify\Api;

use Phpackage\Epayco\Apify\Client;

class AbstractApi
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
