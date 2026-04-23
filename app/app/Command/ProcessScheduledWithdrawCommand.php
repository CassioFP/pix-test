<?php

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;
use Hyperf\Di\Annotation\Inject;

#[Command]
class ProcessScheduledWithdrawCommand extends HyperfCommand
{
    #[Inject]
    protected LoggerInterface $logger;
    protected ?string $name = 'withdraw:process-scheduled';

    public function handle()
    {
        $withdraws = Db::select("
            SELECT * FROM account_withdraw
            WHERE status = 'pending'
            AND scheduled = 1
            AND scheduled_for <= NOW()
        ");

        foreach ($withdraws as $withdraw) {
            try {
                Db::transaction(function () use ($withdraw) {

                    // lock do saque
                    $row = Db::selectOne(
                        "SELECT * FROM account_withdraw WHERE id = ? FOR UPDATE",
                        [$withdraw->id]
                    );

                    if ($row->status !== 'pending') {
                        return;
                    }

                    // lock da conta
                    $account = Db::selectOne(
                        "SELECT * FROM account WHERE id = ? FOR UPDATE",
                        [$row->account_id]
                    );

                    if ($account->balance < $row->amount) {
                        Db::update(
                            "UPDATE account_withdraw 
                             SET status = 'error', error = 1, error_reason = 'Saldo insuficiente'
                             WHERE id = ?",
                            [$row->id]
                        );
                        return;
                    }

                    // debita saldo
                    $newBalance = $account->balance - $row->amount;

                    Db::update(
                        "UPDATE account SET balance = ? WHERE id = ?",
                        [$newBalance, $account->id]
                    );

                    // marca como sucesso
                    Db::update(
                        "UPDATE account_withdraw
                         SET status = 'success', done = 1, processed_at = NOW()
                         WHERE id = ?",
                        [$row->id]
                    );
                });

            } catch (\Throwable $e) {
                Db::update(
                    "UPDATE account_withdraw 
                     SET status = 'error', error = 1, error_reason = ?
                     WHERE id = ?",
                    [$e->getMessage(), $withdraw->id]
                );
            }
        }
    }
}
