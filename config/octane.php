<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    |
    | Octane supports FrankenPHP, Swoole, RoadRunner, and Open Swoole. We
    | will use the default server when none is specified. Uncomment one
    | of the following lines to use the server you prefer.
    |
    */

    'server' => env('OCTANE_SERVER', 'frankenphp'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS
    |--------------------------------------------------------------------------
    |
    | Set this to true to enable HTTPS proxy headers when using FrankenPHP
    | behind a load balancer that terminates HTTPS connections.
    |
    */

    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Listeners
    |--------------------------------------------------------------------------
    |
    | All of the extension listeners for Octane's event dispatcher are
    | listed here. You can add or remove listeners based on what your
    | application needs. The listeners are invoked on every request.
    |
    */

    'listeners' => [
        \Laravel\Octane\Listeners\FlushRegisteredContainerCallbacks::class,
        \Laravel\Octane\Listeners\FlushMiddlewareStack::class,
        \Laravel\Octane\Listeners\EnsureNormalRequestGlobals::class,
        \Laravel\Octane\Listeners\EnableTerminableMiddleware::class,
        \Laravel\Octane\Listeners\PrepareForStaticInvocation::class,
        \Laravel\Octane\Listeners\EnsureRequestsDoNotWaitForBags::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Workers
    |--------------------------------------------------------------------------
    |
    | This value sets the maximum number of workers to run on the Octane
    | server. When a worker process is started, it will continue serving
    | requests until it is killed or restarted. You may adjust this.
    |
    */

    'workers' => env('OCTANE_WORKERS', 4),

    /*
    |--------------------------------------------------------------------------
    | Max Requests
    |--------------------------------------------------------------------------
    |
    | This value sets the maximum number of requests that each worker may
    | handle before the worker is restarted. This is useful when your
    | application has memory leaks or other issues that accumulate.
    |
    */

    'max_requests' => env('OCTANE_MAX_REQUESTS', 1000),

    /*
    |--------------------------------------------------------------------------
    | Tick Interval
    |--------------------------------------------------------------------------
    |
    | This value determines the number of seconds between ticks for the
    | server's event loop. This controls how often the server checks
    | for signals and other events that should be processed.
    |
    */

    'tick_interval' => env('OCTANE_TICK_INTERVAL', 10),

    /*
    |--------------------------------------------------------------------------
    | Warm / Warmers
    |--------------------------------------------------------------------------
    |
    | This option enables you to configure the service warmers that are
    | run before the server starts. This is useful to ensure that the
    | application is ready to serve incoming requests.
    |
    */

    'warm' => true,

    'warmers' => [
        \Laravel\Octane\Warmers\PreloadConfiguration::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Static Prefix
    |--------------------------------------------------------------------------
    |
    | This option determines the prefix for the application's static
    | assets. This is typically /storage when running the application
    | with the FrankenPHP server.
    |
    */

    'static_prefix' => env('OCTANE_STATIC_PREFIX', '/storage'),

    /*
    |--------------------------------------------------------------------------
    | Open Swoole
    |--------------------------------------------------------------------------
    |
    | The following options configure the Open Swoole and Swoole settings
    | used by Octane. You may change these values as required.
    |
    */

    'swoole' => [
        'dispatch_func' => 'Octane\\Swoole\\Dispatchers\\AutoDispatcher',
        'daemonize' => false,
        'backlog' => 128,
        'max_request_time' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | RoadRunner
    |--------------------------------------------------------------------------
    |
    | The following options configure the RoadRunner and RR plugins used
    | by Octane. You may change these values as required.
    |
    */

    'roadrunner' => [
        'host' => env('OCTANE_RR_HOST', '127.0.0.1'),
        'http_port' => env('OCTANE_RR_HTTP_PORT', 8000),
        'rpc_port' => env('OCTANE_RR_RPC_PORT', 6001),
        'grpc_port' => env('OCTANE_RR_GRPC_PORT', 9001),
        'max_connections' => env('OCTANE_RR_MAX_CONNECTIONS', 0),
        'max_queue_size' => env('OCTANE_RR_MAX_QUEUE_SIZE', 1024),
    ],

];
