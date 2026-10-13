<?php

namespace App\NativeComponents;

use App\Actions\EndSchoolYear;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;

class Settings extends NativeComponent
{
    public function navTitle(): string
    {
        return 'Settings';
    }

    public function confirmEndSchoolYear(): void
    {
        Dialog::alert('End school year?', 'Every student and their testing history will be permanently deleted. Classes and songs are kept. This cannot be undone.', [
            ['label' => 'Cancel', 'style' => 'cancel'],
            ['label' => 'Delete everything', 'style' => 'destructive'],
        ])->id('end-school-year');
    }

    #[On(ButtonPressed::class)]
    public function onAlertButton(int $index, string $label, ?string $id = null): void
    {
        if ($id !== 'end-school-year' || $label !== 'Delete everything') {
            return;
        }

        $deleted = app(EndSchoolYear::class)->handle();

        Dialog::toast("{$deleted['students']} students and {$deleted['attempts']} attempts deleted.");
    }

    public function render(): Element
    {
        return $this->view('settings');
    }
}
