<?php

declare(strict_types=1);

namespace App\Livewire\Goals;

use App\Models\Goal;
use App\Models\Milestone;
use App\Services\TelegramService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Goals & Milestones — LifeOS')]
class GoalIndex extends Component
{
    use WithPagination;

    public string $filterStatus = 'all';

    public string $filterCategory = 'all';

    public bool $showGoalModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingGoalId = null;

    public ?int $editingGoalId = null;

    public string $title = '';

    public string $description = '';

    public string $category = 'personal';

    public string $target_date = '';

    public string $target_value = '';

    public string $current_value = '0';

    public string $unit = '';

    public string $status = 'in_progress';

    /**
     * Quick progress input buffer keyed by goal ID.
     *
     * @var array<int, string|float|int>
     */
    public array $quickProgress = [];

    /**
     * Inline new milestone title buffer keyed by goal ID.
     *
     * @var array<int, string>
     */
    public array $newMilestoneTitle = [];

    /**
     * Inline new milestone due date buffer keyed by goal ID.
     *
     * @var array<int, string>
     */
    public array $newMilestoneDueDate = [];

    public function mount(): void
    {
        $this->syncQuickProgressInputs();
    }

    /**
     * Synchronize quick progress values from the user's goals.
     */
    private function syncQuickProgressInputs(): void
    {
        $goals = Goal::query()
            ->where('user_id', Auth::id())
            ->get(['id', 'current_value']);

        foreach ($goals as $goal) {
            $this->quickProgress[$goal->id] = (string) (float) $goal->current_value;
        }
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Goal>
     */
    #[Computed]
    public function goals(): LengthAwarePaginator
    {
        return Goal::query()
            ->with(['milestones' => fn ($query) => $query->orderBy('is_completed')->orderBy('due_date')])
            ->where('user_id', Auth::id())
            ->when($this->filterStatus !== 'all', fn ($query) => $query->where('status', $this->filterStatus))
            ->when($this->filterCategory !== 'all', fn ($query) => $query->where('category', $this->filterCategory))
            ->latest('id')
            ->paginate(10);
    }

    /**
     * Open modal for creating a new Goal.
     */
    public function openCreateModal(): void
    {
        $this->resetGoalForm();
        $this->showGoalModal = true;
    }

    /**
     * Open modal for editing an existing Goal.
     */
    public function editGoal(int $goalId): void
    {
        $goal = Goal::query()
            ->where('user_id', Auth::id())
            ->findOrFail($goalId);

        $this->editingGoalId = $goal->id;
        $this->title = $goal->title;
        $this->description = (string) ($goal->description ?? '');
        $this->category = $goal->category;
        $this->target_date = $goal->target_date ? $goal->target_date->format('Y-m-d') : '';
        $this->target_value = $goal->target_value !== null ? (string) (float) $goal->target_value : '';
        $this->current_value = (string) (float) $goal->current_value;
        $this->unit = (string) ($goal->unit ?? '');
        $this->status = $goal->status;
        $this->showGoalModal = true;
    }

    /**
     * Close the Create/Edit Goal modal and clear form state.
     */
    public function closeGoalModal(): void
    {
        $this->showGoalModal = false;
        $this->resetGoalForm();
    }

    /**
     * Store a new Goal or update an existing Goal.
     */
    public function saveGoal(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', 'in:personal,financial,career,health,spiritual'],
            'target_date' => ['nullable', 'date'],
            'target_value' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:in_progress,completed,paused'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'category' => $validated['category'],
            'target_date' => $validated['target_date'] !== '' ? $validated['target_date'] : null,
            'target_value' => $validated['target_value'] !== '' && $validated['target_value'] !== null
                ? (float) $validated['target_value']
                : null,
            'current_value' => $validated['current_value'] !== '' && $validated['current_value'] !== null
                ? (float) $validated['current_value']
                : 0.0,
            'unit' => $validated['unit'] !== '' ? $validated['unit'] : null,
            'status' => $validated['status'],
        ];

        if ($this->editingGoalId !== null) {
            $goal = Goal::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingGoalId);

            $goal->update($payload);
            $this->quickProgress[$goal->id] = (string) (float) $goal->current_value;

            Flux::toast(variant: 'success', text: 'Goal updated successfully.');
        } else {
            $goal = Goal::query()->create([
                ...$payload,
                'user_id' => (int) Auth::id(),
            ]);

            $this->quickProgress[$goal->id] = (string) (float) $goal->current_value;

            Flux::toast(variant: 'success', text: 'New goal created successfully.');
        }

        unset($this->goals);
        $this->closeGoalModal();
    }

    /**
     * Reactive Quick Progress Updater for numeric target goals.
     */
    public function updateProgress(int $goalId): void
    {
        $rawValue = $this->quickProgress[$goalId] ?? 0;

        if (! is_numeric($rawValue) || (float) $rawValue < 0) {
            Flux::toast(variant: 'danger', text: 'Progress value must be a positive number.');

            return;
        }

        $goal = Goal::query()
            ->where('user_id', Auth::id())
            ->findOrFail($goalId);

        $newValue = (float) $rawValue;
        $status = $goal->status;

        if ($goal->target_value !== null && (float) $goal->target_value > 0 && $newValue >= (float) $goal->target_value) {
            $status = 'completed';
        } elseif ($status === 'completed' && $goal->target_value !== null && $newValue < (float) $goal->target_value) {
            $status = 'in_progress';
        }

        $goal->update([
            'current_value' => $newValue,
            'status' => $status,
        ]);

        $this->quickProgress[$goal->id] = (string) $newValue;
        unset($this->goals);

        Flux::toast(variant: 'success', text: 'Goal progress updated.');
    }

    /**
     * One-click reactive Milestone completion toggle.
     */
    public function toggleMilestone(int $milestoneId): void
    {
        $milestone = Milestone::query()
            ->whereHas('goal', fn ($query) => $query->where('user_id', Auth::id()))
            ->findOrFail($milestoneId);

        $newCompletedState = ! $milestone->is_completed;

        $milestone->update([
            'is_completed' => $newCompletedState,
            'completed_at' => $newCompletedState ? Carbon::now() : null,
        ]);

        $goal = $milestone->goal()->with('milestones')->first();

        if ($goal !== null && ($goal->target_value === null || (float) $goal->target_value <= 0)) {
            $allCompleted = $goal->milestones->isNotEmpty()
                && $goal->milestones->every(fn (Milestone $m): bool => $m->is_completed);

            if ($allCompleted && $goal->status !== 'completed') {
                $goal->update(['status' => 'completed']);
            } elseif (! $allCompleted && $goal->status === 'completed') {
                $goal->update(['status' => 'in_progress']);
            }
        }

        unset($this->goals);
    }

    /**
     * Add a new milestone directly to a Goal card.
     */
    public function addMilestone(int $goalId): void
    {
        $title = trim((string) ($this->newMilestoneTitle[$goalId] ?? ''));
        $dueDate = trim((string) ($this->newMilestoneDueDate[$goalId] ?? ''));

        if ($title === '') {
            return;
        }

        $goal = Goal::query()
            ->where('user_id', Auth::id())
            ->findOrFail($goalId);

        $goal->milestones()->create([
            'title' => $title,
            'due_date' => $dueDate !== '' ? $dueDate : null,
            'is_completed' => false,
        ]);

        $this->newMilestoneTitle[$goalId] = '';
        $this->newMilestoneDueDate[$goalId] = '';

        unset($this->goals);

        Flux::toast(variant: 'success', text: 'Milestone added successfully.');
    }

    /**
     * Delete a milestone from a Goal card.
     */
    public function deleteMilestone(int $milestoneId): void
    {
        $milestone = Milestone::query()
            ->whereHas('goal', fn ($query) => $query->where('user_id', Auth::id()))
            ->findOrFail($milestoneId);

        $milestone->delete();
        unset($this->goals);

        Flux::toast(variant: 'success', text: 'Milestone deleted.');
    }

    public function confirmDeleteGoal(int $goalId): void
    {
        $this->deletingGoalId = $goalId;
        $this->showDeleteModal = true;
    }

    public function executeDeleteGoal(): void
    {
        if ($this->deletingGoalId === null) {
            return;
        }

        $goal = Goal::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->deletingGoalId);

        $goalId = $goal->id;
        $goal->delete();

        unset($this->quickProgress[$goalId], $this->goals);
        $this->deletingGoalId = null;
        $this->showDeleteModal = false;

        Flux::toast(variant: 'success', text: 'Goal deleted successfully.');
    }

    /**
     * Delete a Goal and its cascading milestones.
     */
    public function deleteGoal(int $goalId): void
    {
        $this->confirmDeleteGoal($goalId);
    }

    /**
     * Send an instant Telegram reminder for a specific Goal via TelegramService.
     */
    public function sendReminder(int $goalId, TelegramService $telegramService): void
    {
        $goal = Goal::query()
            ->with('milestones')
            ->where('user_id', Auth::id())
            ->findOrFail($goalId);

        if ($telegramService->sendGoalReminder($goal)) {
            Flux::toast(variant: 'success', text: 'Telegram reminder sent successfully!');
        } else {
            Flux::toast(variant: 'warning', text: 'Telegram not configured (TELEGRAM_BOT_TOKEN / CHAT_ID).');
        }
    }

    private function resetGoalForm(): void
    {
        $this->editingGoalId = null;
        $this->title = '';
        $this->description = '';
        $this->category = 'personal';
        $this->target_date = '';
        $this->target_value = '';
        $this->current_value = '0';
        $this->unit = '';
        $this->status = 'in_progress';
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.goals.goal-index');
    }
}
