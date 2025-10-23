<?php

namespace App\Livewire\Pages;

use App\Models\Course;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CourseShow extends Component
{
    public Course $course;
}
