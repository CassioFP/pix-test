<?php
use function Hyperf\Support\make;

file_put_contents('/tmp/crontab_loaded.log', 'ok' . PHP_EOL, FILE_APPEND);
return [
    [
        'name' => 'process-withdraw',
        'rule' => '* * * * *',
        'callback' => function () {
            make(\App\Command\ProcessScheduledWithdrawCommand::class)->handle();
        },
        'enable' => true,

    ],
];
