<?php

namespace App\Concerns;

use Closure;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;

trait ConfirmsActions
{
    /**
     * Ask before a destructive action. $onConfirm runs, bound to this screen, only when the
     * destructive button is pressed.
     */
    protected function confirm(string $id, string $title, string $message, Closure $onConfirm, string $confirmLabel = 'Delete'): void
    {
        Dialog::alert($title, $message, [
            ['label' => 'Cancel', 'style' => 'cancel'],
            ['label' => $confirmLabel, 'style' => 'destructive'],
        ])->id($id)->buttonPressed(function (ButtonPressed $event) use ($onConfirm): void {
            if ($event->index === 1) {
                $onConfirm();
            }
        });
    }
}
