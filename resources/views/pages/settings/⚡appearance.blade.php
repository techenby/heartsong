<?php

use App\Actions\EndSchoolYear;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Settings')] class extends Component {
    public function endSchoolYear(EndSchoolYear $endSchoolYear): void
    {
        $deleted = $endSchoolYear->handle();

        Flux::toast(variant: 'success', text: __(':students students and :attempts attempts deleted.', $deleted));
    }
}; ?>

<section class="w-full space-y-10">
    @include('partials.settings-heading')

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Choose light or dark mode for Heartsong')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-pages::settings.layout>

    <x-pages::settings.layout :heading="__('End school year')" :subheading="__('Permanently delete every student and their testing history. Classes and songs are kept so you can import next year\'s rosters.')">
        <flux:button
            wire:click="endSchoolYear"
            wire:confirm.prompt="{{ __('This permanently deletes every student and attempt and cannot be undone.\n\nType DELETE to confirm|DELETE') }}"
            variant="danger"
            icon="trash"
        >{{ __('End school year') }}</flux:button>
    </x-pages::settings.layout>
</section>
