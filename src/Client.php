<?php

namespace Phpackage\Epayco\Apify;

use Phpackage\Epayco\Apify\Api\BillCollect;
use Phpackage\Epayco\Apify\Exception\AuthenticationException;
use Requests;
use Requests_Auth_Basic;
use Requests_Exception;
use Requests_Session;

final class Client
{
    public const BASE_URL = 'https://apify.epayco.co/';

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Requests_Session
     */
    private $session;

    public function __construct(string $user, string $password, ?string $baseUrl = null)
    {
        $this->user = $user;
        $this->password = $password;

        $this->session = new Requests_Session($baseUrl ?? self::BASE_URL, [
            'User-Agent' => 'phpackage/epayco-apify-php-sdk',
            'Content-Type' => 'application/json',
        ]);
    }

    public function get(
        string $url,
        array $data = [],
        array $headers = [],
        array $options = []
    ) {
        return $this->request(Requests::GET, $url, $data, $headers, $options);
    }

    private function request(
        string $type,
        string $url,
        array $data = [],
        array $headers = [],
        array $options = []
    ): ?array {
        $this->login();

        $response = $this
            ->session
            ->request($url, $headers, json_encode($data), $type, $options);

        if ($response->success
            && 200 === $response->status_code
            && is_array($body = json_decode($response->body, true))
        ) {
            return $body;
        }

        return null;
    }

    /**
     * @throws AuthenticationException
     */
    private function login(): void
    {
        if (isset($this->session->headers['Authorization'])) {
            return;
        }

        try {
            $auth = new Requests_Auth_Basic([
                $this->user,
                $this->password,
            ]);
        } catch (Requests_Exception $exception) {
        }

        $response = $this->session->post('/login', [], [], [
            'auth' => $auth,
        ]);

        if (!$response->success || 200 !== $response->status_code) {
            throw new AuthenticationException();
        }

        $body = json_decode($response->body, true);

        if (is_array($body)
            && isset($body['token'])
            && is_string($body['token'])
            && !empty($body['token'])
        ) {
            $this->session->headers = array_merge($this->session->headers, [
                'Authorization' => 'Bearer ' . $body['token'],
            ]);

            return;
        }

        $errorMessage = '';

        if (is_array($body)
            && isset($body['error'])
            && is_string($body['error'])
            && !empty($body['error'])
        ) {
            $errorMessage = $body['error'];
        }

        throw new AuthenticationException($errorMessage);
    }

    public function post(
        string $url,
        array $data = [],
        array $headers = [],
        array $options = []
    ) {
        return $this->request(Requests::POST, $url, $data, $headers, $options);
    }

    public function billCollect(): BillCollect
    {
        return new BillCollect($this);
    }
}
