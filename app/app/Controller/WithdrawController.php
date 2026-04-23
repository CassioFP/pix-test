<?php

namespace App\Controller;

use App\DTO\WithdrawRequestDTO;
use App\Service\WithdrawService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class WithdrawController
{
    public function __construct(
        private WithdrawService $service
    ) {}

    #[PostMapping(path: "/account/{accountId}/balance/withdraw")]
    public function withdraw(string $accountId, ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();

        $dto = new WithdrawRequestDTO($data);

        $result = $this->service->withdraw($accountId, $dto);

        return [
            'success' => true,
            'data' => $result,
        ];
    }
}
