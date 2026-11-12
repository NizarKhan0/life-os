<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Goal;
use App\Models\Meeting;
use App\Models\Milestone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramService
{
    private string $botToken;

    private string $defaultChatId;

    public function __construct()
    {
        $this->botToken = (string) config('services.telegram.bot_token', '');
        $this->defaultChatId = (string) config('services.telegram.default_chat_id', '');
    }

    /**
     * Check whether Telegram credentials are configured.
     */
    public function isConfigured(?string $chatId = null): bool
    {
        $targetChatId = $chatId ?? $this->defaultChatId;

        return $this->botToken !== '' && $targetChatId !== '';
    }

    /**
     * Send a message to a Telegram chat via the Bot API.
     */
    public function sendMessage(string $message, ?string $chatId = null, string $parseMode = 'HTML'): bool
    {
        $targetChatId = $chatId ?? $this->defaultChatId;

        if (! $this->isConfigured($targetChatId)) {
            Log::info('TelegramService: Credentials not configured. Message logged locally.', [
                'message_preview' => mb_substr($message, 0, 160),
            ]);

            return false;
        }

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                [
                    'chat_id' => $targetChatId,
                    'text' => $message,
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => true,
                ]
            );

            if (! $response->successful()) {
                Log::warning('TelegramService: Failed to send Telegram message.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('TelegramService: Exception while sending Telegram message.', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format and send a reminder for an active Goal.
     */
    public function sendGoalReminder(Goal $goal, ?string $chatId = null): bool
    {
        $progress = number_format($goal->progressPercentage(), 1);
        $daysRemaining = $goal->daysRemaining();

        $daysText = match (true) {
            $daysRemaining === null => 'Tiada tarikh sasaran',
            $daysRemaining < 0 => 'Lewat '.abs($daysRemaining).' hari',
            $daysRemaining === 0 => 'Hari terakhir (Hari ini!)',
            default => "{$daysRemaining} hari lagi",
        };

        $pendingMilestones = $goal->milestones
            ->where('is_completed', false)
            ->take(3)
            ->map(fn (Milestone $m): string => '  • '.e($m->title))
            ->implode("\n");

        $lines = [
            '🎯 <b>Peringatan Matlamat (LifeOS)</b>',
            '',
            '📌 <b>'.e($goal->title).'</b>',
            '🏷 Kategori: <code>'.e(ucfirst($goal->category)).'</code>',
            "📊 Kemajuan: <b>{$progress}%</b>",
            "⏳ Baki Masa: <b>{$daysText}</b>",
        ];

        if ($goal->target_value !== null && (float) $goal->target_value > 0) {
            $unit = $goal->unit ? ' '.e($goal->unit) : '';
            $currentFormatted = number_format((float) $goal->current_value, 2);
            $targetFormatted = number_format((float) $goal->target_value, 2);
            $lines[] = "💰 Sasaran: <b>{$currentFormatted} / {$targetFormatted}{$unit}</b>";
        }

        if ($pendingMilestones !== '') {
            $lines[] = '';
            $lines[] = '📋 <b>Milestone Belum Selesai:</b>';
            $lines[] = $pendingMilestones;
        }

        return $this->sendMessage(implode("\n", $lines), $chatId);
    }

    /**
     * Format and send a reminder for a scheduled Meeting.
     */
    public function sendMeetingReminder(Meeting $meeting, ?int $remindBeforeMinutes = null, ?string $chatId = null): bool
    {
        $startFormatted = $meeting->start_time->format('d M Y, h:i A');
        $endFormatted = $meeting->end_time ? ' - '.$meeting->end_time->format('h:i A') : '';

        $lines = [
            '🔔 <b>Peringatan Mesyuarat (LifeOS)</b>',
            '',
            '📅 <b>'.e($meeting->title).'</b>',
            "🕒 Masa: <b>{$startFormatted}{$endFormatted}</b>",
        ];

        if ($remindBeforeMinutes !== null) {
            $lines[] = "⏰ Peringatan: <b>{$remindBeforeMinutes} minit sebelum bermula</b>";
        }

        if ($meeting->location_or_link !== null && $meeting->location_or_link !== '') {
            $lines[] = '📍 Lokasi / Pautan: '.e($meeting->location_or_link);
        }

        if ($meeting->description !== null && $meeting->description !== '') {
            $lines[] = '';
            $lines[] = '📝 '.e($meeting->description);
        }

        return $this->sendMessage(implode("\n", $lines), $chatId);
    }
}
