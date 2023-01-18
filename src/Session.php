<?php

declare(strict_types=1);

namespace Conia\Session;

use Conia\Session\OutOfBoundsException;
use Conia\Session\RuntimeException;

class Session
{
    /**
     * @psalm-param non-empty-string $flashMessagesKey
     * @psalm-param non-empty-string $rememberedUriKey
     */
    public function __construct(
        protected readonly string $name = '',
        protected readonly string $flashMessagesKey = 'flash_messages',
        protected readonly string $rememberedUriKey = 'remembered_uri',
    ) {
    }

//  session_set_cookie_params(
//     int $lifetime_or_options,
//     ?string $path = null,
//     ?string $domain = null,
//     ?bool $secure = null,
//     ?bool $httponly = null
// ): bool
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent($file, $line)) {
                if ($this->name) {
                    session_name($this->name);
                }
                // session_cache_limiter(false);

                if (!session_start()) {
                    // @codeCoverageIgnoreStart
                    throw new RuntimeException(__METHOD__ . 'session_start failed.');
                    // @codeCoverageIgnoreEnd
                }
            } else {
                // Cannot be provoked in the test suit
                // @codeCoverageIgnoreStart
                throw new RuntimeException(
                    __METHOD__ . 'Session started after headers sent. File: ' .
                        $file . ' line: ' . $line
                );
                // @codeCoverageIgnoreEnd
            }
        }
    }

    public function forget(): void
    {
        // Unset all of the session variables.
        global $_SESSION;
        $_SESSION = [];

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                (string)$params['path'],
                (string)$params['domain'],
                (bool)$params['secure'],
                (bool)$params['httponly']
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }

    /** @psalm-param non-empty-string $key */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key)) {
            return $_SESSION[$key];
        }
        if (func_num_args() > 1) {
            return $default;
        }

        throw new OutOfBoundsException(
            "The session key '{$key}' does not exist"
        );
    }

    /**
     * @psalm-suppress MixedAssignment
     *
     * @psalm-param non-empty-string $key
     * */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /** @psalm-param non-empty-string $key */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /** @psalm-param non-empty-string $key */
    public function unset(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function active(): bool
    {
        return session_status() == PHP_SESSION_ACTIVE;
    }

    public function regenerate(): void
    {
        if ($this->active()) {
            session_regenerate_id(true);
        }
    }

    public function flash(
        string $message,
        string $queue = 'default',
    ): void {
        $fmKey = $this->flashMessagesKey;

        if (isset($_SESSION[$fmKey]) && is_array($_SESSION[$fmKey])) {
            $_SESSION[$fmKey][] = [
                'message' => htmlspecialchars($message),
                'queue' => htmlspecialchars($queue),
            ];

            return;
        }

        $_SESSION[$fmKey] = [[
            'message' => htmlspecialchars($message),
            'queue' => htmlspecialchars($queue),
        ]];
    }

    public function popFlashes(?string $queue = null): array
    {
        $fmKey = $this->flashMessagesKey;

        if ($queue === null) {
            $flashes = $_SESSION[$fmKey];
            assert(is_array($flashes));
            $_SESSION[$fmKey] = [];
        } else {
            $key = 0;
            $keys = [];
            $flashes = [];

            foreach ($_SESSION[$fmKey] as $flash) {
                assert(isset($flash['queue']));
                assert(isset($flash['message']));

                if ($flash['queue'] === $queue) {
                    $flashes[] = $flash;
                    $keys[] = $key;
                }

                $key++;
            }

            foreach (array_reverse($keys) as $key) {
                if (
                    ($_SESSION[$fmKey] ?? null)
                    && is_array($_SESSION[$fmKey])
                ) {
                    if (isset($_SESSION[$fmKey][$key])) {
                        unset($_SESSION[$fmKey][$key]);
                    }
                }
            }
        }

        return $flashes;
    }

    public function hasFlashes(?string $queue = null): bool
    {
        /** @var array */
        $messages = $_SESSION[$this->flashMessagesKey] ?? [];

        if ($queue) {
            return count(array_filter(
                $messages,
                fn (array $f) => $f['queue'] === $queue,
            )) > 0;
        }

        return count($messages) > 0;
    }

    public function rememberUri(
        string $uri,
        int $expires = 3600,
    ): void {
        $rememberedUri = [
            'uri' => $uri,
            'expires' => time() + $expires,
        ];
        $_SESSION[$this->rememberedUriKey] = $rememberedUri;
    }

    public function rememberedUri(): string
    {
        /** @var null|array{uri: string, expires: int} */
        $rememberedUri = $_SESSION[$this->rememberedUriKey] ?? null;

        if ($rememberedUri) {
            if ($rememberedUri['expires'] > time()) {
                $uri = $rememberedUri['uri'];
                unset($_SESSION[$this->rememberedUriKey]);

                if (filter_var($uri, FILTER_VALIDATE_URL)) {
                    return $uri;
                }
            }

            unset($_SESSION[$this->rememberedUriKey]);
        }

        return '/';
    }
}
