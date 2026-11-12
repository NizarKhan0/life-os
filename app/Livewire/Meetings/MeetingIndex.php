<?php

declare(strict_types=1);

namespace App\Livewire\Meetings;

use App\Models\Meeting;
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

#[Title('Meetings & Time Management — LifeOS')]
class MeetingIndex extends Component
{
    use WithPagination;

    public string $activeTab = 'upcoming';

    public bool $showMeetingModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingMeetingId = null;

    public ?int $editingMeetingId = null;

    public string $title = '';

    public string $description = '';

    public string $location_or_link = '';

    public string $start_time = '';

    public string $end_time = '';

    public string $status = 'scheduled';

    public int $remind_before_minutes = 15;

    /**
     * @return LengthAwarePaginator<int, Meeting>
     */
    #[Computed]
    public function meetings(): LengthAwarePaginator
    {
        $now = Carbon::now();

        return Meeting::query()
            ->with('reminders')
            ->where('user_id', Auth::id())
            ->when(
                $this->activeTab === 'upcoming',
                fn ($query) => $query->where('start_time', '>=', $now)->orderBy('start_time'),
                fn ($query) => $query->where('start_time', '<', $now)->latest('id')
            )
            ->paginate(10);
    }

    #[Computed]
    public function upcomingCount(): int
    {
        return Meeting::query()
            ->where('user_id', Auth::id())
            ->where('start_time', '>=', Carbon::now())
            ->count();
    }

    #[Computed]
    public function pastCount(): int
    {
        return Meeting::query()
            ->where('user_id', Auth::id())
            ->where('start_time', '<', Carbon::now())
            ->count();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['upcoming', 'past'], true)) {
            $this->activeTab = $tab;
            $this->resetPage();
            unset($this->meetings);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetMeetingForm();
        $this->start_time = Carbon::now()->addHour()->startOfHour()->format('Y-m-d\TH:i');
        $this->end_time = Carbon::now()->addHours(2)->startOfHour()->format('Y-m-d\TH:i');
        $this->showMeetingModal = true;
    }

    public function editMeeting(int $meetingId): void
    {
        $meeting = Meeting::query()
            ->with('reminders')
            ->where('user_id', Auth::id())
            ->findOrFail($meetingId);

        $firstReminder = $meeting->reminders->first();

        $this->editingMeetingId = $meeting->id;
        $this->title = $meeting->title;
        $this->description = (string) ($meeting->description ?? '');
        $this->location_or_link = (string) ($meeting->location_or_link ?? '');
        $this->start_time = $meeting->start_time->format('Y-m-d\TH:i');
        $this->end_time = $meeting->end_time ? $meeting->end_time->format('Y-m-d\TH:i') : '';
        $this->status = $meeting->status;
        $this->remind_before_minutes = $firstReminder !== null ? $firstReminder->remind_before_minutes : 15;
        $this->showMeetingModal = true;
    }

    public function closeMeetingModal(): void
    {
        $this->showMeetingModal = false;
        $this->resetMeetingForm();
    }

    public function saveMeeting(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location_or_link' => ['nullable', 'string', 'max:500'],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'status' => ['required', 'string', 'in:scheduled,completed,cancelled'],
            'remind_before_minutes' => ['required', 'integer', 'in:5,10,15,30,60,120'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'location_or_link' => $validated['location_or_link'] !== '' ? $validated['location_or_link'] : null,
            'start_time' => Carbon::parse($validated['start_time']),
            'end_time' => $validated['end_time'] !== '' && $validated['end_time'] !== null
                ? Carbon::parse($validated['end_time'])
                : null,
            'status' => $validated['status'],
        ];

        if ($this->editingMeetingId !== null) {
            $meeting = Meeting::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingMeetingId);

            $meeting->update($payload);

            $reminder = $meeting->reminders()->first();
            if ($reminder !== null) {
                $reminder->update([
                    'remind_before_minutes' => (int) $validated['remind_before_minutes'],
                    'is_sent' => false,
                    'sent_at' => null,
                ]);
            } else {
                $meeting->reminders()->create([
                    'remind_before_minutes' => (int) $validated['remind_before_minutes'],
                    'channel' => 'telegram',
                    'is_sent' => false,
                ]);
            }

            Flux::toast(variant: 'success', text: 'Meeting updated successfully.');
        } else {
            $meeting = Meeting::query()->create([
                ...$payload,
                'user_id' => (int) Auth::id(),
            ]);

            $meeting->reminders()->create([
                'remind_before_minutes' => (int) $validated['remind_before_minutes'],
                'channel' => 'telegram',
                'is_sent' => false,
            ]);

            Flux::toast(variant: 'success', text: 'New meeting scheduled successfully.');
        }

        unset($this->meetings, $this->upcomingCount, $this->pastCount);
        $this->closeMeetingModal();
    }

    public function confirmDeleteMeeting(int $meetingId): void
    {
        $this->deletingMeetingId = $meetingId;
        $this->showDeleteModal = true;
    }

    public function executeDeleteMeeting(): void
    {
        if ($this->deletingMeetingId === null) {
            return;
        }

        $meeting = Meeting::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->deletingMeetingId);

        $meeting->delete();
        $this->deletingMeetingId = null;
        $this->showDeleteModal = false;

        unset($this->meetings, $this->upcomingCount, $this->pastCount);

        Flux::toast(variant: 'success', text: 'Meeting deleted successfully.');
    }

    public function deleteMeeting(int $meetingId): void
    {
        $this->confirmDeleteMeeting($meetingId);
    }

    public function markCompleted(int $meetingId): void
    {
        $meeting = Meeting::query()
            ->where('user_id', Auth::id())
            ->findOrFail($meetingId);

        $meeting->update([
            'status' => $meeting->status === 'completed' ? 'scheduled' : 'completed',
        ]);

        unset($this->meetings);
    }

    /**
     * Trigger a test Telegram alert for a specific meeting or general connection test.
     */
    public function testTelegramAlert(?int $meetingId, TelegramService $telegramService): void
    {
        if ($meetingId !== null) {
            $meeting = Meeting::query()
                ->with('reminders')
                ->where('user_id', Auth::id())
                ->findOrFail($meetingId);

            $firstReminder = $meeting->reminders->first();
            $remindMinutes = $firstReminder !== null ? $firstReminder->remind_before_minutes : 15;

            $sent = $telegramService->sendMeetingReminder($meeting, $remindMinutes);

            if ($firstReminder !== null) {
                $firstReminder->update([
                    'is_sent' => true,
                    'sent_at' => Carbon::now(),
                ]);
            }

            unset($this->meetings);

            if ($sent) {
                Flux::toast(variant: 'success', text: 'Test alert sent to Telegram successfully!');
            } else {
                Flux::toast(variant: 'warning', text: 'Simulation successful (🔔 Sent). Set TELEGRAM_BOT_TOKEN & CHAT_ID in .env for live messages.');
            }

            return;
        }

        $sent = $telegramService->sendMessage(
            "🔔 <b>Test Telegram Alert — LifeOS Meetings</b>\n\nSambungan bot Telegram anda berfungsi dengan baik!"
        );

        if ($sent) {
            Flux::toast(variant: 'success', text: 'Test alert sent to Telegram successfully!');
        } else {
            Flux::toast(variant: 'warning', text: 'Please set TELEGRAM_BOT_TOKEN and TELEGRAM_DEFAULT_CHAT_ID in .env.');
        }
    }

    private function resetMeetingForm(): void
    {
        $this->editingMeetingId = null;
        $this->title = '';
        $this->description = '';
        $this->location_or_link = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->status = 'scheduled';
        $this->remind_before_minutes = 15;
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.meetings.meeting-index');
    }
}
