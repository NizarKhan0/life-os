<?php

declare(strict_types=1);

namespace App\Livewire\Reviews;

use App\Models\BrainDump;
use App\Models\WeeklyReview;
use App\Services\TelegramService;
use App\Services\WeeklyReviewService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Weekly Review & Brain Dump — LifeOS')]
class WeeklyReviewIndex extends Component
{
    use WithPagination;

    // Brain Dump Quick Bar
    public string $dumpContent = '';

    public ?int $editingDumpId = null;

    public bool $showProcessedDumps = false;

    // Week selector (Monday date, Y-m-d)
    public string $weekStartDate = '';

    // Reflection form (3 questions)
    public string $wins = '';

    public string $challenges = '';

    public string $improvements = '';

    public function mount(WeeklyReviewService $reviewService): void
    {
        $this->weekStartDate = $reviewService->weekStart()->toDateString();
        $this->loadReviewForm();
    }

    /**
     * @return array{tasks_completed: int, tasks_pending: int, habit_checkins: int, total_expense: float, total_income: float, meetings_held: int, goals_completed: int}
     */
    #[Computed]
    public function snapshot(): array
    {
        return app(WeeklyReviewService::class)->buildSnapshot(
            (int) Auth::id(),
            Carbon::parse($this->weekStartDate)
        );
    }

    /**
     * @return Collection<int, BrainDump>
     */
    #[Computed]
    public function brainDumps(): Collection
    {
        return BrainDump::query()
            ->where('user_id', Auth::id())
            ->when(! $this->showProcessedDumps, fn ($query) => $query->where('is_processed', false))
            ->orderBy('is_processed')
            ->latest()
            ->get();
    }

    /**
     * @return LengthAwarePaginator<int, WeeklyReview>
     */
    #[Computed]
    public function pastReviews(): LengthAwarePaginator
    {
        return WeeklyReview::query()
            ->where('user_id', Auth::id())
            ->whereDate('week_start_date', '!=', $this->weekStartDate)
            ->latest('id')
            ->paginate(10);
    }

    #[Computed]
    public function isCurrentWeek(): bool
    {
        return $this->weekStartDate === app(WeeklyReviewService::class)->weekStart()->toDateString();
    }

    public function editDump(int $dumpId): void
    {
        $dump = BrainDump::query()
            ->where('user_id', Auth::id())
            ->findOrFail($dumpId);

        $this->editingDumpId = $dump->id;
        $this->dumpContent = $dump->content;
    }

    public function cancelEditDump(): void
    {
        $this->editingDumpId = null;
        $this->dumpContent = '';
    }

    /**
     * Brain Dump Quick Bar: press Enter to save instantly.
     */
    public function saveDump(): void
    {
        $content = trim($this->dumpContent);

        if ($content === '') {
            return;
        }

        $this->validate([
            'dumpContent' => ['required', 'string', 'max:2000'],
        ]);

        if ($this->editingDumpId !== null) {
            $dump = BrainDump::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingDumpId);

            $dump->update(['content' => $content]);
            $this->editingDumpId = null;
            $toastText = 'Brain dump note updated.';
        } else {
            BrainDump::query()->create([
                'user_id' => (int) Auth::id(),
                'content' => $content,
                'is_processed' => false,
            ]);
            $toastText = 'Brain dump note saved.';
        }

        $this->dumpContent = '';
        unset($this->brainDumps);

        Flux::toast(variant: 'success', text: $toastText);
    }

    public function toggleDumpProcessed(int $dumpId): void
    {
        $dump = BrainDump::query()
            ->where('user_id', Auth::id())
            ->findOrFail($dumpId);

        $newState = ! $dump->is_processed;
        $dump->update(['is_processed' => $newState]);

        unset($this->brainDumps);

        Flux::toast(
            variant: $newState ? 'success' : 'warning',
            text: $newState ? 'Note marked as processed.' : 'Note reopened.'
        );
    }

    public function deleteDump(int $dumpId): void
    {
        $dump = BrainDump::query()
            ->where('user_id', Auth::id())
            ->findOrFail($dumpId);

        $dump->delete();
        unset($this->brainDumps);

        Flux::toast(variant: 'success', text: 'Brain dump note deleted.');
    }

    public function previousWeek(): void
    {
        $this->weekStartDate = Carbon::parse($this->weekStartDate)->subWeek()->toDateString();
        $this->refreshWeek();
    }

    public function nextWeek(): void
    {
        $next = Carbon::parse($this->weekStartDate)->addWeek();
        $current = app(WeeklyReviewService::class)->weekStart();

        if ($next->greaterThan($current)) {
            return;
        }

        $this->weekStartDate = $next->toDateString();
        $this->refreshWeek();
    }

    public function openReview(string $weekStart): void
    {
        $this->weekStartDate = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)->toDateString();
        $this->refreshWeek();
    }

    /**
     * Save the 3-question reflection along with the automatic metrics snapshot.
     */
    public function saveReview(): void
    {
        $validated = $this->validate([
            'wins' => ['nullable', 'string', 'max:3000'],
            'challenges' => ['nullable', 'string', 'max:3000'],
            'improvements' => ['nullable', 'string', 'max:3000'],
        ]);

        WeeklyReview::query()->updateOrCreate(
            [
                'user_id' => (int) Auth::id(),
                'week_start_date' => $this->weekStartDate,
            ],
            [
                'wins' => trim((string) $validated['wins']) !== '' ? trim((string) $validated['wins']) : null,
                'challenges' => trim((string) $validated['challenges']) !== '' ? trim((string) $validated['challenges']) : null,
                'improvements' => trim((string) $validated['improvements']) !== '' ? trim((string) $validated['improvements']) : null,
                'metrics_snapshot' => $this->snapshot(),
            ]
        );

        unset($this->pastReviews);

        Flux::toast(variant: 'success', text: 'Weekly review saved successfully.');
    }

    public function sendTelegramReminder(TelegramService $telegramService): void
    {
        $unprocessed = BrainDump::query()
            ->where('user_id', Auth::id())
            ->where('is_processed', false)
            ->count();

        if ($telegramService->sendWeeklyReviewReminder($this->snapshot(), $unprocessed)) {
            Flux::toast(variant: 'success', text: 'Weekly snapshot sent to Telegram!');
        } else {
            Flux::toast(variant: 'warning', text: 'Simulation recorded in log (Set TELEGRAM_BOT_TOKEN in .env).');
        }
    }

    private function refreshWeek(): void
    {
        unset($this->snapshot, $this->pastReviews, $this->isCurrentWeek);
        $this->loadReviewForm();
    }

    private function loadReviewForm(): void
    {
        $review = WeeklyReview::query()
            ->where('user_id', Auth::id())
            ->whereDate('week_start_date', $this->weekStartDate)
            ->first();

        $this->wins = $review !== null ? (string) $review->wins : '';
        $this->challenges = $review !== null ? (string) $review->challenges : '';
        $this->improvements = $review !== null ? (string) $review->improvements : '';
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.reviews.weekly-review-index');
    }
}
