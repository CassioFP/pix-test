<?php

namespace App\Service;

use App\DTO\WithdrawRequestDTO;
use App\Repository\AccountRepository;
use App\Repository\WithdrawRepository;
use Hyperf\DbConnection\Db;
use App\Exception\InsufficientBalanceException;
use App\Exception\InvalidWithdrawScheduleException;
use App\Exception\AccountNotFoundException;
use Ramsey\Uuid\Uuid;
use function Hyperf\Support\make;

class WithdrawService
{
    public function __construct(
        private AccountRepository $accountRepo,
        private WithdrawRepository $withdrawRepo
    ) {}

    public function withdraw(string $accountId, WithdrawRequestDTO $dto): array
    {
        return Db::transaction(function () use ($accountId, $dto) {

            $account = $this->accountRepo->findForUpdate($accountId);
            if (!$account) {
                throw new AccountNotFoundException();
            }

            // valida agendamento no passado
            if ($dto->isScheduled() && strtotime($dto->schedule) < time()) {
                throw new InvalidWithdrawScheduleException();
            }

            $withdrawId = Uuid::uuid4()->toString();

            $isScheduled = $dto->isScheduled();

            // valida saldo somente se for imediato
            if (!$isScheduled && $account->balance < $dto->amount) {
                throw new InsufficientBalanceException();
            }

            // cria saque
            $this->withdrawRepo->create([
                'id' => $withdrawId,
                'account_id' => $accountId,
                'method' => $dto->method,
                'amount' => $dto->amount,
                'scheduled' => $isScheduled,
                'scheduled_for' => $dto->schedule,
                'done' => false,
                'error' => false,
                'status' => $isScheduled ? 'pending' : 'processing',
            ]);

            // cria PIX
            $this->withdrawRepo->createPix([
                'withdraw_id' => $withdrawId,
                'type' => $dto->pixType,
                'key' => $dto->pixKey,
            ]);

            if (!$isScheduled) {
                $newBalance = $account->balance - $dto->amount;

                $this->accountRepo->updateBalance($accountId, $newBalance);

                Db::update(
                    "UPDATE account_withdraw 
                     SET status = 'success', done = 1, processed_at = NOW()
                     WHERE id = ?",
                    [$withdrawId]
                );

                go(function () use ($dto) {
                    try {
                        make(\App\Service\EmailService::class)
                            ->send($dto->pixKey, $dto->amount);
                    } catch (\Throwable $e) {
                        error_log('Erro ao enviar email: ' . $e->getMessage());
                    }
                });
            }

            return [
                'withdrawId' => $withdrawId,
                'status' => $isScheduled ? 'pending' : 'success'
            ];
        });
    }
}
