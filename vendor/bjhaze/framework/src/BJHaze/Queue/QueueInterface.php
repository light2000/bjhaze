<?php
/**
 *
 * Queue Interface.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Queue;

interface QueueInterface
{

    /**
     * Adds an element to the queue.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function enqueue ($value);

    /**
     * Dequeues a node from the queue.
     *
     * @return mixed
     */
    public function dequeue ();
}
