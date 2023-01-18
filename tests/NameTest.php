<?php

declare(strict_types=1);

use Conia\Session\Session;

test('Unnamed session', function () {
    $session = new Session();
    $session->start();

    expect($session->name())->toBe('PHPSESSID');

    $session->forget();
});


test('Named session', function () {
    $session = new Session('test');
    $session->start();

    expect($session->name())->toBe('test');

    $session->forget();
});
