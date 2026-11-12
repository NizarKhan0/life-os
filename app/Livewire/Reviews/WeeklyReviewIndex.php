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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Weekly Review & Brain Dump — LifeOS')]
class WeeklyReviewIndex extends Component
{
    // Brain Dump Quick Bar
    public string $dumpContent = '';

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
     * @return Collection<int, WeeklyReview>
     */
    #[Computed]
    public function pastReviews(): Collection
    {
        return WeeklyReview::query()
            ->where('user_id', Auth::id())
            ->whereDate('week_start_date', '!=', $this->weekStartDate)
            ->orderByDesc('week_start_date')
            ->limit(6)
            ->get();
    }

    #[Computed]
    public function isCurrentWeek(): bool
    {
        return $this->weekStartDate === app(WeeklyReviewService::class)->weekStart()->toDateString();
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

        BrainDump::query()->create([
            'user_id' => (int) Auth::id(),
            'content' => $content,
            'is_processed' => false,
        ]);

        $this->dumpContent = '';
        unset($this->brainDumps);

        Flux::toast(variant: 'success', text: 'Nota Brain Dump disimpan.');
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
            text: $newState ? 'Nota ditanda sebagai telah diproses.' : 'Nota dibuka semula.'
        );
    }

    public function deleteDump(int $dumpId): void
    {
        $dump = BrainDump::query()
            ->where('user_id', Auth::id())
            ->findOrFail($dumpId);

        $dump->delete();
        unset($this->brainDumps);

        Flux::toast(variant: 'success', text: 'Nota Brain Dump telah dipadam.');
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

        Flux::toast(variant: 'success', text: 'Refleksi mingguan berjaya disimpan.');
    }

    public function sendTelegramReminder(TelegramService $telegramService): void
    {
        $unprocessed = BrainDump::query()
            ->where('user_id', Auth::id())
            ->where('is_processed', false)
            ->count();

        if ($telegramService->sendWeeklyReviewReminder($this->snapshot(), $unprocessed)) {
            Flux::toast(variant: 'success', text: 'Snapshot mingguan dihantar ke Telegram!');
        } else {
            Flux::toast(variant: 'warning', text: 'Simulasi direkodkan dalam log (Tetapkan TELEGRAM_BOT_TOKEN dalam .env).');
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
