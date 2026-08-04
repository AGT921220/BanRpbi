<?php

namespace App\Features\Clients\Application;

final readonly class ClientHeader
{
    public function __construct(
        public int $id,
        public string $fullName,
        public ?string $email,
        public ?string $phone,
        public ?string $company,
        public ?string $createdAt,
        public bool $hasContract,
        public bool $canUpdate,
        public bool $canDelete,
    ) {}

    /**
     * @return array{
     *     id: int,
     *     full_name: string,
     *     email: string|null,
     *     phone: string|null,
     *     company: string|null,
     *     created_at: string|null,
     *     has_contract: bool,
     *     can_update: bool,
     *     can_delete: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->fullName,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'created_at' => $this->createdAt,
            'has_contract' => $this->hasContract,
            'can_update' => $this->canUpdate,
            'can_delete' => $this->canDelete,
        ];
    }
}
