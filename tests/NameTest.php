<?php

declare(strict_types=1);

use Conia\Session\Session;
use Conia\Session\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    session_name('PHPSESSID');
});

test('Named session', function () {
    $session = new Session('test');
    $session->start();

    expect($session->name())->toBe('test');

    $session->forget();
});


test('Unnamed session', function () {
    $session = new Session();
    $session->start();

    expect($session->name())->toBe('PHPSESSID');

    $session->forget();
});
