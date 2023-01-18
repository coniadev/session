<?php

declare(strict_types=1);

use Conia\Session\OutOfBoundsException;
use Conia\Session\Session;

test('Session set/has/get', function () {
    $session = new Session();
    $session->set('Chuck', 'Schuldiner');

    expect($session->has('Chuck'))->toBe(true);
    expect($session->get('Chuck'))->toBe('Schuldiner');
});


test('Session unset', function () {
    $session = new Session();
    $session->set('Chuck', 'Schuldiner');

    expect($session->get('Chuck'))->toBe('Schuldiner');
    expect($session->has('Chuck'))->toBe(true);

    $session->unset('Chuck');

    expect($session->get('Chuck', null))->toBe(null);
    expect($session->has('Chuck'))->toBe(false);
});


test('Session throws when missing', function () {
    $session = new Session();
    $session->get('To exist in this world may be a mistake');
})->throws(OutOfBoundsException::class, 'To exist in this world may be a mistake');


test('Session get default', function () {
    $session = new Session();
    expect($session->get('Rick', 'Rozz'))->toBe('Rozz');
});


test('Flash messages all', function () {
    $session = new Session();

    expect($session->hasFlashes())->toBe(false);

    $session->flash('Your existence is a script');
    $session->flash('Time is a thing we must accept', 'error');

    expect($session->hasFlashes())->toBe(true);
    expect($session->hasFlashes('error'))->toBe(true);
    expect($session->hasFlashes('info'))->toBe(false);

    $flashes = $session->popFlashes();
    expect(count($flashes))->toBe(2);
    expect($flashes[0]['queue'])->toBe('default');
    expect($flashes[1]['queue'])->toBe('error');
});


test('Flash messages queue', function () {
    $session = new Session();

    expect($session->hasFlashes())->toBe(false);

    $session->flash('Your existence is a script');
    $session->flash('Time is a thing we must accept', 'error');

    $flashes = $session->popFlashes('error');
    expect(count($flashes))->toBe(1);
    expect($flashes[0]['queue'])->toBe('error');

    $flashes = $session->popFlashes();
    expect(count($flashes))->toBe(1);
    expect($flashes[0]['queue'])->toBe('default');
});


test('Remember URI', function () {
    $session = new Session();
    $session->rememberUri('https://www.example.com/albums');

    expect($session->rememberedUri())->toBe('https://www.example.com/albums');
    expect($session->rememberedUri())->toBe('/');

    // Test to return '/' when expired
    $session->rememberUri('https://www.example.com/albums', -3600);
    expect($session->rememberedUri())->toBe('/');
});


test('Session run start/forget/regenerate', function () {
    // Merely runs the code without effect.
    // Can't be tested properly.
    $session = new Session();
    $session->start();
    $session->set('Chuck', 'Schuldiner');

    expect($session->get('Chuck'))->toBe('Schuldiner');

    $session->forget();

    expect($session->has('Chuck'))->toBe(false);

    $session->regenerate();
});
