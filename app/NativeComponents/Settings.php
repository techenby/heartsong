<?php

namespace App\NativeComponents;

use App\Actions\EndSchoolYear;
use App\Actions\MoveToPosition;
use App\Concerns\ConfirmsActions;
use App\Concerns\HasValidationErrors;
use App\Concerns\ReordersRows;
use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Dialog;

/**
 * @property-read Collection<int, Period> $periods
 */
class Settings extends NativeComponent
{
    use ConfirmsActions, HasValidationErrors, ReordersRows;

    /** The period sheet is open for adding (no id) or editing (an id). */
    public bool $showPeriod = false;

    public ?int $editingPeriodId = null;

    public string $periodName = '';

    /** "H:i" */
    public string $startsAt = '';

    /** "H:i" */
    public string $endsAt = '';

    public function navTitle(): string
    {
        return 'Settings';
    }

    /**
     * @return Collection<int, Period>
     */
    #[Computed(persist: true)]
    public function periods(): Collection
    {
        return Period::query()->ordered()->withCount('meetings')->get();
    }

    /**
     * @return array<int, NavAction>
     */
    public function periodMenu(int $periodId): array
    {
        return [
            NavAction::make("edit-period-{$periodId}")->icon('edit')->label('Edit')->press("editPeriod({$periodId})"),
            ...$this->moveMenuItems('movePeriod', $periodId),
            NavAction::divider(),
            NavAction::make("delete-period-{$periodId}")->icon('delete')->label('Delete')->press("confirmDeletePeriod({$periodId})")->destructive(),
        ];
    }

    public function addPeriod(): void
    {
        $previousEnd = $this->periods->last()->ends_at ?? '08:00';

        $this->openPeriodSheet(null, '', $previousEnd, Carbon::parse($previousEnd)->addMinutes(45)->format('H:i'));
    }

    public function editPeriod(int $periodId): void
    {
        $period = Period::findOrFail($periodId);

        $this->openPeriodSheet($period->id, $period->name, $period->starts_at, $period->ends_at);
    }

    public function savePeriod(): void
    {
        $attributes = $this->validateOrRecord(
            ['name' => trim($this->periodName), 'starts_at' => $this->startsAt, 'ends_at' => $this->endsAt],
            [
                'name' => ['required', 'string', 'max:50', Rule::unique('periods', 'name')->ignore($this->editingPeriodId)],
                'starts_at' => ['required', 'date_format:H:i'],
                'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            ],
            ['ends_at.after' => 'The period has to end after it starts.'],
        );

        if (! $attributes) {
            return;
        }

        $this->editingPeriodId
            ? Period::findOrFail($this->editingPeriodId)->update($attributes)
            : Period::create($attributes);

        $this->closePeriod();
        unset($this->periods);
    }

    public function closePeriod(): void
    {
        $this->showPeriod = false;
        $this->editingPeriodId = null;
        $this->errors = [];
    }

    public function movePeriod(int $periodId, int $offset): void
    {
        app(MoveToPosition::class)->by(Period::query()->ordered()->get(), $periodId, $offset);

        unset($this->periods);
    }

    public function confirmDeletePeriod(int $periodId): void
    {
        $period = Period::withCount('meetings')->findOrFail($periodId);

        $message = $period->meetings_count
            ? trans_choice('Classes will lose the :count meeting time scheduled during it.|Classes will lose the :count meeting times scheduled during it.', $period->meetings_count)
            : 'No classes meet during it.';

        $this->confirm("delete-period-{$period->id}", "Delete {$period->name}?", $message, function () use ($period): void {
            $period->delete();
            unset($this->periods);
        });
    }

    public function confirmEndSchoolYear(): void
    {
        $this->confirm('end-school-year', 'End school year?', 'Every student and their testing history will be permanently deleted. Classes and songs are kept. This cannot be undone.', function (): void {
            $deleted = app(EndSchoolYear::class)->handle();

            Dialog::toast("{$deleted['students']} students and {$deleted['attempts']} attempts deleted.");
        }, 'Delete everything');
    }

    protected function openPeriodSheet(?int $periodId, string $name, string $startsAt, string $endsAt): void
    {
        $this->editingPeriodId = $periodId;
        $this->periodName = $name;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->errors = [];
        $this->showPeriod = true;
    }

    public function render(): Element
    {
        return $this->view('settings');
    }
}
