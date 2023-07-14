<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskInProgressReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Task $task)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function withDelay()
    {
        return [
            'mail' => now()->addHours(config('app.in_progress_reminder_wait_hours')),
        ];
    }

    public function shouldSend(object $notifiable): bool
    {
        return $this->task->isInProgress();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/tasks');
        return (new MailMessage)
            ->subject('Task Completion Reminder.')
            ->markdown('mail.task.in-progress-reminder', ['url' => $url, 'task' => $this->task, 'user' => $this->task->user]);
    }
}
