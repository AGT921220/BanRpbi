<?php

namespace App\Features\Services\Domain;

final readonly class ServiceDetailsResponse
{
    /**
     * @var ServiceDetail[]
     */
    private array $data;

    private int $total;

    public function __construct(int $total, ServiceDetail ...$data)
    {
        $this->data = $data;
        $this->total = $total;
    }

    /**
     * @return ServiceDetail[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'data' => array_map(
                fn (ServiceDetail $detail) => $detail->toArray(),
                $this->getData()
            ),
        ];
    }
}
