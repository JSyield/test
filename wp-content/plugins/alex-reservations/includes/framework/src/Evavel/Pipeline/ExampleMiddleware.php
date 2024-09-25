<?php

namespace Evavel\Pipeline;

class ExampleMiddleware
{
    public function handle($something, $next){

        $something .= " Reservations";

        return $next($something);
    }
}

/*

$pipeline = new Evavel\Pipeline\Pipeline();

$pipeline
  ->send('hello')
  ->through([
    Evavel\Pipeline\ExampleMiddleware::class,
    function($string, $next){
      $string = ucwords($string);
      return $next($string);
    },
  ])
  ->via('handle')
  ->then(function($string){
    dump($string);
  });


*/
