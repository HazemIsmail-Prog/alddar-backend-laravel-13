<?php

namespace App\Events\Comments;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CommentCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Comment $comment,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $commentableType = $this->comment->commentable_type;
        $commentableId = $this->comment->commentable_id;
        $departmentId = $this->comment->commentable->department_id;
        $technicianId = $this->comment->commentable->technician_id;

        $channels = [];


        $channels[] = new Channel('comments.' . $commentableType . '.' . $commentableId);

        if($departmentId) {
            $channels[] = new Channel('department.' . $departmentId);
        }

        if($technicianId) {
            $channels[] = new Channel('technician.' . $technicianId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'comment.created';
    }
}
