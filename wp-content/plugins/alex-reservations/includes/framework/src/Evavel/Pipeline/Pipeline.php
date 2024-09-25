<?php

namespace Evavel\Pipeline;

use Closure;
use Exception;

class Pipeline
{
    protected $passable;
    protected $pipes = [];
    protected $method = 'handle';

    public function __construct(){}

    public function send($passable)
    {
        $this->passable = $passable;

        return $this;
    }

    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    public function via($method)
    {
        $this->method = $method;

        return $this;
    }

    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes()), $this->carry(), $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    protected function pipes()
    {
        return $this->pipes;
    }

    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Exception $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                
                try {

                    // function
                    if (is_callable($pipe)) {
                        return $pipe($passable, $stack);
                    }

                    // class string
                    elseif(! is_object($pipe)){
                        $result = $this->parsePipeString($pipe);
                        $name = $result[0];
                        $parameters = $result[1];

                        // Resolve the class base on the string
                        //$pipe = $this->getContainer()->make($name);
                        $pipe = new $name();

                        $parameters = array_merge([$passable, $stack], $parameters);
                    }

                    // object
                    else {

                        $parameters = [$passable, $stack];
                    }

                    // Call as method inside the class or as function
                    $carry = method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}(...$parameters)
                        : $pipe(...$parameters);

                    return $this->handleCarry($carry);
                }
                catch (Exception $e) {
                    $this->handleException($passable, $e);
                }


            };
        };
    }

    protected function handleCarry($carry)
    {
        return $carry;
    }

    protected function parsePipeString($pipe)
    {
        $result = array_pad(explode(':', $pipe, 2), 2, []);
        $name = $result[0];
        $parameters = $result[1];

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    protected function handleException($passable, Exception $e){
        //throw $e;
        dump($e); exit();
    }

}
