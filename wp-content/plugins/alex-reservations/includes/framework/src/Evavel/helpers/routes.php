<?php

use Evavel\Http\Route;

/**
 * Create a Route object from GET
 *
 * @param string $uri
 * @param string $action
 * @param string $context
 * @return Evavel\Http\Route
 */
function evavel_route_get($uri, $action, $context = \Evavel\Enums\Context::INDEX) {
    return Route::get($uri, $action)->context($context);
}

/**
 * Create a Route object from POST
 *
 * @param string $uri
 * @param string $action
 * @param string $context
 * @return Evavel\Http\Route
 */
function evavel_route_post($uri, $action, $context) {
    return Route::post($uri, $action)->context($context);
}

/**
 * Create a Route object from DELETE
 *
 * @param string $uri
 * @param string $action
 * @param string $context
 * @return Evavel\Http\Route
 */
function evavel_route_delete($uri, $action, $context) {
    return Route::delete($uri, $action)->context($context);
}
