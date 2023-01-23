<?php

declare(strict_types=1);

namespace Conia\Session;

use Conia\Session\OutOfBoundsException;
use Conia\Session\RuntimeException;
use SessionHandlerInterface;

/** @psalm-api */
class Session
{
    public const FLASH = 'conia_flash_messages';
    public const REMEMBER = 'conia_remembered_uri';

    public function __construct(
        protected readonly string $name = '',
        protected readonly array $options = [],
        protected readonly ?SessionHandlerInterface $handler = null,
    ) {
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent($file, $line)) {
                if ($this->name) {
                    session_name($this->name);
                }

                if ($this->handler) {
                    session_set_save_handler($this->handler, true);
                }

                session_cache_limiter('');

                if (!session_start($this->options)) {
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
        session_unset(); // same as $_SESSION = [];

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

    public function name(): string
    {
        return session_name();
    }

    public function id(): string
    {
        return session_id();
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
        if (!$this->active()) {
            throw new RuntimeException('Session not started');
        }

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
        if (!$this->active()) {
            throw new RuntimeException('Session not started');
        }

        if (isset($_SESSION[self::FLASH]) && is_array($_SESSION[self::FLASH])) {
            $_SESSION[self::FLASH][] = [
                'message' => htmlspecialchars($message),
                'queue' => htmlspecialchars($queue),
            ];

            return;
        }

        $_SESSION[self::FLASH] = [[
            'message' => htmlspecialchars($message),
            'queue' => htmlspecialchars($queue),
        ]];
    }

    public function popFlashes(?string $queue = null): array
    {
        if ($queue === null) {
            $flashes = $_SESSION[self::FLASH];
            assert(is_array($flashes));
            $_SESSION[self::FLASH] = [];
        } else {
            $key = 0;
            $keys = [];
            $flashes = [];

            foreach ($_SESSION[self::FLASH] as $flash) {
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
                    ($_SESSION[self::FLASH] ?? null)
                    && is_array($_SESSION[self::FLASH])
                ) {
                    if (isset($_SESSION[self::FLASH][$key])) {
                        unset($_SESSION[self::FLASH][$key]);
                    }
                }
            }
        }

        return $flashes;
    }

    public function hasFlashes(?string $queue = null): bool
    {
        /** @var array */
        $messages = $_SESSION[self::FLASH] ?? [];

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
        $_SESSION[self::REMEMBER] = $rememberedUri;
    }

    public function rememberedUri(): string
    {
        /** @var null|array{uri: string, expires: int} */
        $rememberedUri = $_SESSION[self::REMEMBER] ?? null;

        if ($rememberedUri) {
            if ($rememberedUri['expires'] > time()) {
                $uri = $rememberedUri['uri'];
                unset($_SESSION[self::REMEMBER]);

                if (filter_var($uri, FILTER_VALIDATE_URL)) {
                    return $uri;
                }
            }

            unset($_SESSION[self::REMEMBER]);
        }

        return '/';
    }
}
