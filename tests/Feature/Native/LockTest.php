<?php

use App\NativeComponents\Lock;
use Native\Mobile\Events\Biometric\Completed;
use Native\Mobile\Testing\Native;

test('the app opens on the lock screen and asks for Touch ID', function () {
    Native::visit('/')
        ->assertScreen(Lock::class)
        ->assertSee('Unlock to see your classes.')
        ->assertNativeCalled('Biometric.Prompt');
});

test('a successful unlock opens the classes', function () {
    Native::test(Lock::class)
        ->emitNative(Completed::class, ['success' => true, 'id' => 'unlock'])
        ->assertReplacedWith('/courses');
});

test('a failed unlock stays locked and can be retried', function () {
    Native::test(Lock::class)
        ->emitNative(Completed::class, ['success' => false, 'id' => 'unlock'])
        ->assertNoNavigation()
        ->assertSee("Couldn't unlock. Try again.")
        ->tap('Unlock')
        ->assertDontSee("Couldn't unlock. Try again.")
        ->assertNativeCalledTimes('Biometric.Prompt', 2);
});
