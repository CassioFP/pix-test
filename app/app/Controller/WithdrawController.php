<?php

namespace App\Controller;

use App\DTO\WithdrawRequestDTO;
use App\Service\WithdrawService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpMessage\Stream\SwooleStream;

#[Controller]
class WithdrawController
{
    public function __construct(
        private WithdrawService $service,
        private HttpResponse $response
    ) {}

    #[PostMapping(path: "/account/{accountId}/balance/withdraw")]
    public function withdraw(string $accountId, ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();

        $dto = new WithdrawRequestDTO($data);

        try {
            $result = $this->service->withdraw($accountId, $dto);

            return $this->response->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'error' => $e->getMessage(),
            ])->withStatus($e->getCode() ?: 500);
        }
    }
}
