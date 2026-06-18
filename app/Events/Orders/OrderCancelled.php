<?php

namespace App\Events\Orders;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderCancelled implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public $oldTechnicianId = null,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('orders'),
            new Channel('department.' . $this->order->department_id),
        ];
        
        if($this->oldTechnicianId) {
            $channels[] = new Channel('technician.' . $this->oldTechnicianId);
        }
        return $channels;
        
    }

    public function broadcastAs(): string
    {
        return 'order.cancelled';
    }
}
