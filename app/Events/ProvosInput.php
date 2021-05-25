<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;


class ProvosInput implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    /**
    * The name of the queue connection to use when broadcasting the event.
    *
    * @var string
    */

    static $data=[];
    static $fingerprint='f';

    public $connection = 'redis';

    /**
    * The name of the queue on which to place the broadcasting job.
    *
    * @var string
    */
    public $queue = 'default';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data=[],$fingerprint)
    {
        //
        static::$data=$data;
        static::$fingerprint=$fingerprint;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('provos-channel');
    }

    /*
     * The Event's broadcast name.
     * 
     * @return string
     */
    public function broadcastAs()
    {
        return 'Finger-'.static::$fingerprint;
    }
    
    /*
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return static::$data;
    }
}
