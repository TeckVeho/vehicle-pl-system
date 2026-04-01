<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class ProcessEvents implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $process;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($process)
    {
        $this->process = $process;
    }

    public function broadcastOn()
    {
        return [App::environment() . '_cloud_deface_channel_' . $this->process->id];
    }

    public function broadcastAs()
    {
        return 'cloud_deface_process_event';
    }
}
