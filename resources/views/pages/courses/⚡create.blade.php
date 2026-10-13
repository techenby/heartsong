<?php

use App\Livewire\Forms\CourseForm;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Create class')] class extends Component {
    public CourseForm $form;

    public function addMeeting(): void
    {
        $this->form->addMeeting();
    }

    public function duplicateMeeting(int $index): void
    {
        $this->form->duplicateMeeting($index);
    }

    public function removeMeeting(int $index): void
    {
        $this->form->removeMeeting($index);
    }

    public function save(): void
    {
        $course = $this->form->store();

        $this->redirectRoute('courses.show', $course, navigate: true);
    }
}; ?>

<div class="space-y-6">
    <flux:heading size="xl" level="1">{{ __('Create class') }}</flux:heading>

    @include('partials.courses.form', ['cancelUrl' => route('courses.index')])
</div>
