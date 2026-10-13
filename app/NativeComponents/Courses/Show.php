<?php

namespace App\NativeComponents\Courses;

use App\Actions\ImportRoster;
use App\Concerns\ConfirmsActions;
use App\Concerns\HasValidationErrors;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Validation\ValidationException;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Dialog;
use Smronju\NativephpDocumentPicker\Events\DocumentPicked;
use Smronju\NativephpDocumentPicker\Facades\DocumentPicker;

/**
 * @property-read Collection<int, Student> $students
 * @property-read Student|null $historyStudent
 * @property-read SupportCollection<int, Student> $missingStudents
 */
class Show extends NativeComponent
{
    use ConfirmsActions, HasValidationErrors;

    public Course $course;

    /** The student whose details are being edited; the details sheet is open while this is set. */
    public ?int $editingStudentId = null;

    public string $preferredName = '';

    public string $pronouns = '';

    /** The student whose history is showing; the history sheet is open while this is set. */
    public ?int $historyStudentId = null;

    public bool $showImport = false;

    public ?string $importError = null;

    /** @var list<array{0: string, 1: string}> Names read from the picked roster, waiting on confirmation. */
    public array $importNames = [];

    /** @var array<int, int> Missing students the teacher has marked as having left. */
    public array $withdraw = [];

    public function mount(Course $course): void
    {
        $this->course = $course;
    }

    public function navTitle(): string
    {
        return $this->course->name;
    }

    public function onResume(): void
    {
        $this->course->refresh();
        unset($this->students);
    }

    /**
     * @return Collection<int, Student>
     */
    #[Computed(persist: true)]
    public function students(): Collection
    {
        return $this->course->students()->orderBy('last_name')->orderBy('first_name')->get();
    }

    #[Computed(persist: true)]
    public function historyStudent(): ?Student
    {
        return $this->historyStudentId
            ? $this->course->students()->with(['attempts' => fn ($query) => $query->with('song')->latest('attempted_on')->latest('id')])->find($this->historyStudentId)
            : null;
    }

    /**
     * @return SupportCollection<int, Student>
     */
    #[Computed(persist: true)]
    public function missingStudents(): SupportCollection
    {
        return $this->importNames === []
            ? collect()
            : app(ImportRoster::class)->missingStudents($this->course, $this->importNames);
    }

    /**
     * @return array<int, NavAction>
     */
    public function studentMenu(int $studentId): array
    {
        return [
            NavAction::make("history-{$studentId}")->icon('history', ios: 'clock.arrow.circlepath', android: 'history')->label('History')->press("openHistory({$studentId})"),
            NavAction::make("details-{$studentId}")->icon('edit')->label('Edit details')->press("editStudent({$studentId})"),
            NavAction::divider(),
            NavAction::make("withdraw-{$studentId}")->icon('delete')->label('Mark as left')->press("confirmWithdraw({$studentId})")->destructive(),
        ];
    }

    public function editStudent(int $studentId): void
    {
        $student = $this->course->students()->findOrFail($studentId);

        $this->editingStudentId = $student->id;
        $this->preferredName = $student->preferred_name ?? '';
        $this->pronouns = $student->pronouns ?? '';
        $this->errors = [];
    }

    public function saveStudent(): void
    {
        $valid = $this->validateOrRecord(
            ['preferredName' => $this->preferredName, 'pronouns' => $this->pronouns],
            ['preferredName' => ['nullable', 'string', 'max:100'], 'pronouns' => ['nullable', 'string', 'max:50']],
        );

        if ($valid === null) {
            return;
        }

        $this->course->students()->findOrFail($this->editingStudentId)->update([
            'preferred_name' => trim($this->preferredName) ?: null,
            'pronouns' => trim($this->pronouns) ?: null,
        ]);

        $this->closeDetails();
        unset($this->students);
    }

    public function closeDetails(): void
    {
        $this->editingStudentId = null;
        $this->errors = [];
    }

    public function openHistory(int $studentId): void
    {
        $this->historyStudentId = $this->course->students()->findOrFail($studentId)->id;
        unset($this->historyStudent);
    }

    public function closeHistory(): void
    {
        $this->historyStudentId = null;
    }

    public function deleteAttempt(int $attemptId): void
    {
        Attempt::query()->where('student_id', $this->historyStudentId)->findOrFail($attemptId)->delete();

        unset($this->historyStudent);
    }

    public function confirmWithdraw(int $studentId): void
    {
        $student = $this->course->students()->findOrFail($studentId);

        $this->confirm("withdraw-{$student->id}", "Mark {$student->display_name} as left?", 'They will be permanently deleted along with their testing history. This cannot be undone.', function () use ($student): void {
            $student->delete();
            unset($this->students);
        });
    }

    public function openImport(): void
    {
        $this->resetImport();
        $this->showImport = true;
    }

    public function closeImport(): void
    {
        $this->resetImport();
        $this->showImport = false;
    }

    public function chooseRoster(): void
    {
        $this->importError = null;

        DocumentPicker::pick([
            'text/csv',
            'text/comma-separated-values',
            'text/plain',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    #[On(DocumentPicked::class)]
    public function rosterPicked(?string $path = null, ?string $name = null): void
    {
        // A cancelled or failed pick arrives without a path.
        if (blank($path)) {
            return;
        }

        $importRoster = app(ImportRoster::class);

        try {
            $type = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION)) === 'xlsx' ? 'xlsx' : 'csv';
            $this->importNames = $importRoster->readNames($path, $type);
        } catch (ValidationException $exception) {
            $this->importError = $exception->errors()['roster'][0] ?? 'That file could not be read.';

            return;
        } finally {
            // The picker hands over a temporary copy; the roster's names now only live in the database.
            @unlink($path);
        }

        unset($this->missingStudents);

        if ($this->missingStudents->isEmpty()) {
            $this->import($importRoster);
        }
    }

    public function toggleWithdraw(int $studentId, bool $checked): void
    {
        $this->withdraw = $checked
            ? array_values(array_unique([...$this->withdraw, $studentId]))
            : array_values(array_diff($this->withdraw, [$studentId]));
    }

    public function import(ImportRoster $importRoster): void
    {
        $result = $importRoster->handle($this->course, $this->importNames, $this->withdraw);

        $this->closeImport();
        unset($this->students);

        Dialog::toast("Roster imported: {$result['added']} added, {$result['withdrawn']} deleted.");
    }

    protected function resetImport(): void
    {
        $this->importNames = [];
        $this->withdraw = [];
        $this->importError = null;
        unset($this->missingStudents);
    }

    public function render(): Element
    {
        return $this->view('courses.show');
    }
}
