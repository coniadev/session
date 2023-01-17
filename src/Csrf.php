<?php

declare(strict_types=1);

namespace Conia\Chuck;

class Csrf
{
    public function __construct(
        protected string $sessionKey = 'csrftokens',
        protected string $postKey = 'csrftoken',
        protected string $headerKey = 'HTTP_X_CSRF_TOKEN',
    ) {
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
    }

    public function get(string $page = 'default'): string
    {
        $token = (string)($_SESSION[$this->sessionKey][$page] ?? $this->set($page));

        return $token;
    }

    public function verify(
        string $page = 'default',
        string $token = null
    ): bool {
        if ($token === null) {
            $token = $_POST[$this->postKey] ?? null;
        }

        if ($token === null) {
            if (isset($_SERVER[$this->headerKey])) {
                $token = $_SERVER[$this->headerKey];
            }
        }

        if ($token === null) {
            return false;
        }

        $savedToken = $this->get($page);

        if (empty($savedToken)) {
            return false;
        }

        if (is_string($token) && !empty($token)) {
            return hash_equals($savedToken, $token);
        }

        return false;
    }

    protected function set(string $page = 'default'): string
    {
        assert(isset($_SESSION[$this->sessionKey]) && is_array($_SESSION[$this->sessionKey]));

        $token = base64_encode(random_bytes(32));
        $_SESSION[$this->sessionKey][$page] = $token;

        return $token;
    }
}
