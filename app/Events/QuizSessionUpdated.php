<?php

namespace App\Events;

use App\Models\QuizSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizSessionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public QuizSession $session)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("quiz-session.{$this->session->id}"),
            new PrivateChannel("quiz-admin.{$this->session->quiz_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'quiz.session.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->session->id,
            'quiz_id' => $this->session->quiz_id,
            'code' => $this->session->code,
            'state' => $this->session->state,
            'current_question_id' => $this->session->current_question_id,
            'current_question_position' => $this->session->current_question_position,
            'started_at' => $this->session->started_at?->toIso8601String(),
            'ended_at' => $this->session->ended_at?->toIso8601String(),
        ];
    }
}
