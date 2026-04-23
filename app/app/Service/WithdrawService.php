<?php

namespace App\Service;

use App\DTO\WithdrawRequestDTO;
use App\Repository\AccountRepository;
use App\Repository\WithdrawRepository;
use Hyperf\DbConnection\Db;
use Ramsey\Uuid\Uuid;

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
                throw new \Exception("Conta não encontrada");
            }

            // valida agendamento no passado
            if ($dto->isScheduled() && strtotime($dto->schedule) < time()) {
                throw new \Exception("Data de agendamento inválida");
            }

            $withdrawId = Uuid::uuid4()->toString();

            $isScheduled = $dto->isScheduled();

            // valida saldo somente se for imediato
            if (!$isScheduled && $account->balance < $dto->amount) {
                throw new \Exception("Saldo insuficiente");
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
            }

            return [
                'withdrawId' => $withdrawId,
                'status' => $isScheduled ? 'pending' : 'success'
            ];
        });
    }
}
