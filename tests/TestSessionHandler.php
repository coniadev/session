<?php

namespace Conia\Session\Tests;

use ErrorException;
use SessionHandlerInterface;

class TestSessionHandler implements SessionHandlerInterface
{
    public array $sessions;
    public bool $visited = false;

    public function open(string $savePath, string $sessionName): bool
    {
        $this->sessions = [];
        $this->visited = true;

        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            return $this->sessions[$id];
        } catch (ErrorException) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        $this->sessions[$id] = $data;

        return true;
    }

    public function destroy($id): bool
    {
        unset($this->sessions);

        return true;
    }

    public function gc($maxlifetime): int|false
    {
        return true;
    }
}
