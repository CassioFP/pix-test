<?php

namespace App\Repository;

use Hyperf\DbConnection\Db;
use App\Enum\WithdrawStatus;

class WithdrawRepository
{
    public function create(array $data): void
    {
        Db::insert(
            "INSERT INTO account_withdraw 
            (id, account_id, method, amount, scheduled, scheduled_for, done, error, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $data['id'],
                $data['account_id'],
                $data['method'],
                $data['amount'],
                $data['scheduled'],
                $data['scheduled_for'],
                $data['done'],
                $data['error'],
                $data['status'],
            ]
        );
    }

    public function createPix(array $data): void
    {
        Db::insert(
            "INSERT INTO account_withdraw_pix 
            (account_withdraw_id, type, pix_key)
            VALUES (?, ?, ?)",
            [
                $data['withdraw_id'],
                $data['type'],
                $data['key'],
            ]
        );
    }

    public function registerSuccess(string $withdrawId)
    {
        Db::update(
            "UPDATE account_withdraw 
                SET status = 'success', done = 1, processed_at = NOW()
                WHERE id = ?",
            [$withdrawId]
        );
    }

    public function getScheduledItemsByLimit($limit = null)
    {
        $status = WithdrawStatus::PENDING->value;
        return Db::select("
            SELECT * FROM account_withdraw
            WHERE status = $status
            AND scheduled = 1
            AND scheduled_for <= " . $limit ?? NOW()
        );
    }
}
