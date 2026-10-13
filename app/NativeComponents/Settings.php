<?php

namespace App\NativeComponents;

use App\Actions\EndSchoolYear;
use App\Actions\MoveToPosition;
use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;

/**
 * @property-read Collection<int, Period> $periods
 */
class Settings extends NativeComponent
{
    public bool $showPeriod = false;

    public ?int $editingPeriodId = null;

    public string $periodName = '';

    /** "H:i" */
    public string $startsAt = '';

    /** "H:i" */
    public string $endsAt = '';

    /** @var array<string, array<int, string>> */
    public array $errors = [];

    public function navTitle(): string
    {
        return 'Settings';
    }

    /**
     * @return Collection<int, Period>
     */
    #[Computed]
    public function periods(): Collection
    {
        return Period::query()->ordered()->withCount('meetings')->get();
    }

    public function periodTimes(Period $period): string
    {
        return Carbon::parse($period->starts_at)->format('g:i A').' – '.Carbon::parse($period->ends_at)->format('g:i A');
    }

    /**
     * @return array<int, NavAction>
     */
    public function periodMenu(int $periodId): array
    {
        return [
            NavAction::make("edit-period-{$periodId}")->icon('edit')->label('Edit')->press("editPeriod({$periodId})"),
            NavAction::make("up-period-{$periodId}")->icon('arrow_upward', ios: 'arrow.up', android: 'arrow_upward')->label('Move up')->press("movePeriod({$periodId}, -1)"),
            NavAction::make("down-period-{$periodId}")->icon('arrow_downward', ios: 'arrow.down', android: 'arrow_downward')->label('Move down')->press("movePeriod({$periodId}, 1)"),
            NavAction::divider(),
            NavAction::make("delete-period-{$periodId}")->icon('delete')->label('Delete')->press("confirmDeletePeriod({$periodId})")->destructive(),
        ];
    }

    public function addPeriod(): void
    {
        $previousEnd = $this->periods->last()->ends_at ?? '08:00';

        $this->editingPeriodId = null;
        $this->periodName = '';
        $this->startsAt = $previousEnd;
        $this->endsAt = Carbon::parse($previousEnd)->addMinutes(45)->format('H:i');
        $this->errors = [];
        $this->showPeriod = true;
    }

    public function editPeriod(int $periodId): void
    {
        $period = Period::findOrFail($periodId);

        $this->editingPeriodId = $period->id;
        $this->periodName = $period->name;
        $this->startsAt = $period->starts_at;
        $this->endsAt = $period->ends_at;
        $this->errors = [];
        $this->showPeriod = true;
    }

    public function savePeriod(): void
    {
        $validator = Validator::make(
            ['name' => trim($this->periodName), 'starts_at' => $this->startsAt, 'ends_at' => $this->endsAt],
            [
                'name' => ['required', 'string', 'max:50', Rule::unique('periods', 'name')->ignore($this->editingPeriodId)],
                'starts_at' => ['required', 'date_format:H:i'],
                'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            ],
            ['ends_at.after' => 'The period has to end after it starts.'],
        );

        if ($validator->fails()) {
            $this->errors = $validator->errors()->toArray();

            return;
        }

        $attributes = $validator->validated();

        if ($this->editingPeriodId) {
            Period::findOrFail($this->editingPeriodId)->update($attributes);
        } else {
            Period::create([...$attributes, 'position' => (Period::max('position') ?? -1) + 1]);
        }

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
        $periods = Period::query()->ordered()->get();
        $index = $periods->search(fn (Period $period) => $period->id === $periodId);

        app(MoveToPosition::class)->handle($periods, $periodId, max(0, (int) $index + $offset));

        unset($this->periods);
    }

    public function confirmDeletePeriod(int $periodId): void
    {
        $period = Period::withCount('meetings')->findOrFail($periodId);

        Dialog::alert("Delete {$period->name}?", $period->meetings_count
            ? trans_choice('Classes will lose the :count meeting time scheduled during it.|Classes will lose the :count meeting times scheduled during it.', $period->meetings_count)
            : 'No classes meet during it.', [
                ['label' => 'Cancel', 'style' => 'cancel'],
                ['label' => 'Delete', 'style' => 'destructive'],
            ])->id("delete-period-{$period->id}");
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
        if ($id === 'end-school-year' && $label === 'Delete everything') {
            $deleted = app(EndSchoolYear::class)->handle();

            Dialog::toast("{$deleted['students']} students and {$deleted['attempts']} attempts deleted.");

            return;
        }

        if ($label === 'Delete' && str_starts_with((string) $id, 'delete-period-')) {
            Period::find((int) str($id)->after('delete-period-')->toString())?->delete();

            unset($this->periods);
        }
    }

    public function render(): Element
    {
        return $this->view('settings');
    }
}
