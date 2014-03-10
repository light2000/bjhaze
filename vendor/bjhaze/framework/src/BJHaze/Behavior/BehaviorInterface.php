<?php
/**
 *
 * Behavior Interface.
 * 
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Behavior;

interface BehaviorInterface
{

    /**
     * Run the Behavior in chain
     *
     * @param mixed $action
     * @param array $parameters
     * @param array $before
     * @param array $after
     * @param mixed $result
     * @return void
     */
    public function handle (&$action, array &$parameters, array &$before, array &$after, &$result);
}
