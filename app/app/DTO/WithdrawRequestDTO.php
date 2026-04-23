<?php

namespace App\DTO;

class WithdrawRequestDTO
{
    public string $method;
    public string $pixType;
    public string $pixKey;
    public float $amount;
    public ?string $schedule;

    public function __construct(array $data)
    {
        $this->method = $data['method'];
        $this->pixType = $data['pix']['type'];
        $this->pixKey = $data['pix']['key'];
        $this->amount = $data['amount'];
        $this->schedule = $data['schedule'] ?? null;
    }

    public function isScheduled(): bool
    {
        return !empty($this->schedule);
    }
}
