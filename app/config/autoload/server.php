<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Swoole\Constant;
use Swoole\Timer;

use Hyperf\Framework\Bootstrap\WorkerStartCallback;
use Hyperf\Framework\Bootstrap\PipeMessageCallback;
use Hyperf\Framework\Bootstrap\WorkerExitCallback;
use Hyperf\Framework\Bootstrap\TaskCallback;
use Hyperf\Framework\Bootstrap\FinishCallback;

use function Hyperf\Support\make;

return [
    'mode' => SWOOLE_BASE,

    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
            'options' => [
                'enable_request_lifecycle' => false,
            ],
        ],
    ],

    'settings' => [
        Constant::OPTION_ENABLE_COROUTINE => true,
        Constant::OPTION_WORKER_NUM => swoole_cpu_num(),
        Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/hyperf.pid',
        Constant::OPTION_OPEN_TCP_NODELAY => true,
        Constant::OPTION_MAX_COROUTINE => 100000,
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
        Constant::OPTION_MAX_REQUEST => 100000,
        Constant::OPTION_SOCKET_BUFFER_SIZE => 2 * 1024 * 1024,
        Constant::OPTION_BUFFER_OUTPUT_SIZE => 2 * 1024 * 1024,
    ],

    'callbacks' => [
        Event::ON_WORKER_START => function ($server, $workerId) {
            make(\Hyperf\Framework\Bootstrap\WorkerStartCallback::class)
                ->onWorkerStart($server, $workerId);

            if ($workerId === 0) {
                \Swoole\Timer::tick(60000, function () {
                    try {
                        make(\App\Command\ProcessScheduledWithdrawCommand::class)->handle();
                    } catch (\Throwable $e) {
                        error_log('[CRON] ' . $e->getMessage());
                    }
                });
            }
        },
        Event::ON_PIPE_MESSAGE => [PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [WorkerExitCallback::class, 'onWorkerExit'],
        Event::ON_TASK => [TaskCallback::class, 'onTask'],
        Event::ON_FINISH => [FinishCallback::class, 'onFinish'],
    ],
];