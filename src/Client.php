<?php

namespace Phpackage\Epayco\Apify;

use Requests_Auth_Basic;
use Requests_Session;

final class Client extends Requests_Session
{
    const BASE_URL = 'https://apify.epayco.co/';

    public function __construct(string $user, string $password)
    {
        $options = [
            'auth' => new Requests_Auth_Basic(compact('user', 'password'))
        ];

        parent::__construct(self::BASE_URL, [], [], $options);
    }
}
