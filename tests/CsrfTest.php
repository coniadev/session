<?php

declare(strict_types=1);

use Conia\Session\Csrf;
use Conia\Session\Session;

test('Csrf get creates token', function () {
    $session = new Session();
    $csrf = new Csrf();
    $token = $csrf->get();

    expect($token)->toHaveLength(44);
    expect($session->get('csrftokens')['default'])->toBe($token);

    unset($_SESSION['csrftokens']);
});


test('Csrf verify post', function () {
    $csrf = new Csrf();
    $token = $csrf->get();

    $_POST['csrftoken'] = $token;

    expect($csrf->verify())->toBe(true);

    $_POST['csrftoken'] = 'empty words';

    expect($csrf->verify())->toBe(false);

    unset($_POST['csrftoken'], $_SESSION['csrftokens']);
});


test('Csrf verify header', function () {
    $csrf = new Csrf();
    $token = $csrf->get();

    $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;

    expect($csrf->verify())->toBe(true);

    $_SERVER['HTTP_X_CSRF_TOKEN'] = 'empty words';

    expect($csrf->verify())->toBe(false);

    $_SERVER['HTTP_X_CSRF_TOKEN'] = 666;

    expect($csrf->verify())->toBe(false);

    unset($_SERVER['HTTP_X_CSRF_TOKEN'], $_SESSION['csrftokens']);
});


test('Csrf verify empty session', function () {
    $csrf = new Csrf();
    $token = $csrf->get();

    $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
    $_SESSION['csrftokens']['default'] = '';

    expect($csrf->verify())->toBe(false);

    unset($_SERVER['HTTP_X_CSRF_TOKEN'], $_SESSION['csrftokens']);
});


test('Csrf verify token null', function () {
    $csrf = new Csrf();

    expect($csrf->verify())->toBe(false);
});


test('Csrf get/verify different page', function () {
    $csrf = new Csrf();
    $tokenDefault = $csrf->get();
    $tokenAlbums = $csrf->get('albums');

    $_POST['csrftoken'] = $tokenDefault;

    expect($csrf->verify())->toBe(true);
    expect($csrf->verify('albums'))->toBe(false);

    $_POST['csrftoken'] = $tokenAlbums;

    expect($csrf->verify())->toBe(false);
    expect($csrf->verify('albums'))->toBe(true);

    unset($_POST['csrftoken'], $_SESSION['csrftokens']);
});
