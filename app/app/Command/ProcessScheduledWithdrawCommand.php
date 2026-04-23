<?php

namespace App\Command;

use App\Repository\WithdrawRepository;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;
use Hyperf\Di\Annotation\Inject;
use App\Enum\WithdrawStatus;


#[Command]
class ProcessScheduledWithdrawCommand extends HyperfCommand
{
    #[Inject]
    protected LoggerInterface $logger;
    protected ?string $name = 'withdraw:process-scheduled';
    private WithdrawRepository $withdrawRepository;

    public function handle()
    {
        $withdraws = $this->withdrawRepository->getScheduledItemsByLimit();
        foreach ($withdraws as $withdraw) {
            try {
                Db::transaction(function () use ($withdraw) {

                    // lock do saque
                    $row = Db::selectOne(
                        "SELECT * FROM account_withdraw WHERE id = ? FOR UPDATE",
                        [$withdraw->id]
                    );

                    if ($row->status !== WithdrawStatus::PENDING->value) {
                        return;
                    }

                    // lock da conta
                    $account = Db::selectOne(
                        "SELECT * FROM account WHERE id = ? FOR UPDATE",
                        [$row->account_id]
                    );

                    if ($account->balance < $row->amount) {
                        $errorTextStatus = WithdrawStatus::ERROR->value;
                        Db::update(
                            "UPDATE account_withdraw 
                             SET status = '$errorTextStatus', error = 1, error_reason = 'Saldo insuficiente'
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
                    $successTextStatus = WithdrawStatus::SUCCESS->value;
                    Db::update(
                        "UPDATE account_withdraw
                         SET status = '$successTextStatus', done = 1, processed_at = NOW()
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
