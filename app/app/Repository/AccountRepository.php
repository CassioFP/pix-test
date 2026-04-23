<?php

namespace App\Repository;

use Hyperf\DbConnection\Db;

class AccountRepository
{
    public function findForUpdate(string $id): ?object
    {
        return Db::selectOne(
            "SELECT * FROM account WHERE id = ? FOR UPDATE",
            [$id]
        );
    }

    public function updateBalance(string $id, float $newBalance): void
    {
        Db::update(
            "UPDATE account SET balance = ? WHERE id = ?",
            [$newBalance, $id]
        );
    }
}
