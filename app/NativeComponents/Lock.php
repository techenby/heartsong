<?php

namespace App\NativeComponents;

use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Events\Biometric\Completed;
use Native\Mobile\Facades\Biometrics;

class Lock extends NativeComponent
{
    public bool $failed = false;

    public function mount(): void
    {
        $this->unlock();
    }

    public function unlock(): void
    {
        $this->failed = false;

        // Touch ID, falling back to the iPad's passcode. The result arrives as a Completed event.
        Biometrics::prompt()->id('unlock');
    }

    #[On(Completed::class)]
    public function unlocked(bool $success, ?string $id = null): void
    {
        if ($id !== 'unlock') {
            return;
        }

        if ($success) {
            $this->replace('/courses');

            return;
        }

        $this->failed = true;
    }

    public function render(): Element
    {
        return $this->view('lock');
    }
}
