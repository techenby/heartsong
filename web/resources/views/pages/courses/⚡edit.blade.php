<?php

use App\Livewire\Forms\CourseForm;
use App\Models\Course;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit class')] class extends Component {
    public CourseForm $form;

    public function mount(Course $course): void
    {
        $this->form->setCourse($course);
    }

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
        $this->form->update();

        $this->redirectRoute('courses.show', $this->form->course, navigate: true);
    }
}; ?>

<div class="space-y-6">
    <flux:heading size="xl" level="1">{{ __('Edit class') }}</flux:heading>

    @include('partials.courses.form', ['cancelUrl' => route('courses.show', $form->course)])
</div>
