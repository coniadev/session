<?php

declare(strict_types=1);

namespace Conia\Session\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use SessionHandler;

/**
 * @internal
 *
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    protected function setUp(): void
    {
        parent::setUp();

        session_name('PHPSESSID');
        session_set_save_handler(new SessionHandler(), true);
    }
}
