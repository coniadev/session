<?php

declare(strict_types=1);

use Conia\Session\OutOfBoundsException;
use Conia\Session\Session;

beforeEach(function () {
    $this->session = new Session();
    $this->session->start();
});


afterEach(function () {
    if ($this->session->active()) {
        $this->session->forget();
    }
});


test('Session set/has/get', function () {
    $this->session->set('Chuck', 'Schuldiner');

    expect($this->session->has('Chuck'))->toBe(true);
    expect($this->session->get('Chuck'))->toBe('Schuldiner');
});


test('Session unset', function () {
    $this->session->set('Chuck', 'Schuldiner');

    expect($this->session->get('Chuck'))->toBe('Schuldiner');
    expect($this->session->has('Chuck'))->toBe(true);

    $this->session->unset('Chuck');

    expect($this->session->get('Chuck', null))->toBe(null);
    expect($this->session->has('Chuck'))->toBe(false);
});


test('Session throws when missing', function () {
    $this->session->get('To exist in this world may be a mistake');
})->throws(OutOfBoundsException::class, 'To exist in this world may be a mistake');


test('Session get default', function () {
    expect($this->session->get('Rick', 'Rozz'))->toBe('Rozz');
});


test('Flash messages all', function () {
    expect($this->session->hasFlashes())->toBe(false);

    $this->session->flash('Your existence is a script');
    $this->session->flash('Time is a thing we must accept', 'error');

    expect($this->session->hasFlashes())->toBe(true);
    expect($this->session->hasFlashes('error'))->toBe(true);
    expect($this->session->hasFlashes('info'))->toBe(false);

    $flashes = $this->session->popFlashes();
    expect(count($flashes))->toBe(2);
    expect($flashes[0]['queue'])->toBe('default');
    expect($flashes[1]['queue'])->toBe('error');
});


test('Flash messages queue', function () {
    expect($this->session->hasFlashes())->toBe(false);

    $this->session->flash('Your existence is a script');
    $this->session->flash('Time is a thing we must accept', 'error');

    $flashes = $this->session->popFlashes('error');
    expect(count($flashes))->toBe(1);
    expect($flashes[0]['queue'])->toBe('error');

    $flashes = $this->session->popFlashes();
    expect(count($flashes))->toBe(1);
    expect($flashes[0]['queue'])->toBe('default');
});


test('Remember URI', function () {
    $this->session->rememberUri('https://www.example.com/albums');

    expect($this->session->rememberedUri())->toBe('https://www.example.com/albums');
    expect($this->session->rememberedUri())->toBe('/');

    // Test to return '/' when expired
    $this->session->rememberUri('https://www.example.com/albums', -3600);
    expect($this->session->rememberedUri())->toBe('/');
});


test('Session run start/forget', function () {
    $this->session->start();
    $this->session->set('Chuck', 'Schuldiner');

    expect($this->session->get('Chuck'))->toBe('Schuldiner');

    $this->session->forget();

    expect($this->session->has('Chuck'))->toBe(false);
});


test('Regenerate ID', function () {
    $this->session->start();
    $id = session_id();
    $this->session->regenerate();

    expect(session_id())->not()->toBe($id);
});
