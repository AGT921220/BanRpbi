<?php

namespace App\Http\Requests\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use Illuminate\Foundation\Http\FormRequest;

final class SubmitClientConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionTypes::CLIENTS_ASSIGN_CONTRACTS) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
