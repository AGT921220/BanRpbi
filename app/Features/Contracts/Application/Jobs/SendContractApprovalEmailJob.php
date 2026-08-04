<?php

namespace App\Features\Contracts\Application\Jobs;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\Constants\RoleTypes;
use App\Mail\ClientConfigurationSubmitted;
use App\Models\ClientContract;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendContractApprovalEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private int $clientContractId;

    protected $tries = 3;
    // protected $queue = 'client';
    public function __construct(
        int $clientContractId
    ) {
        $this->clientContractId = $clientContractId;
    }
    public function tags(): array
    {
        return ['contract-approval-email', 'contract-' . $this->clientContractId];
    }

    public function handle()
    {
        $clientContract = ClientContract::with('client')->findOrFail($this->clientContractId);
        $this->sendContractApprovalEmail($clientContract);
    }
    private function sendContractApprovalEmail(ClientContract $clientContract): void
    {
        $enableSendMails = config('app.enable_send_mails', false);

        $emailsToSend = $enableSendMails
            ? $this->getRecipients()
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all()
            : array_filter([
                config('app.default_mail'),
            ]);

        if (empty($emailsToSend)) {
            info('No recipients found for contract approval email.', [
                'contract_id' => $clientContract->id,
                'enable_send_mails' => $enableSendMails,
            ]);

            return;
        }
        $client = $clientContract->client;

        Mail::to($emailsToSend)->send(new ClientConfigurationSubmitted($client));

        // Mail::to($emailsToSend)->send(
        //     new ContractApprove($contract)
        // );
    }
    private function getRecipients(): Collection
    {
        return User::query()
            ->select([
                'users.id',
                'users.email',
            ])
            ->whereNotNull('users.email')
            ->where('users.email', '!=', '')
            ->role([
                RoleTypes::DIRECTOR_GENERAL,
                RoleTypes::DIRECTOR_VENTAS,
            ])
            ->permission(PermissionTypes::CLIENT_CONTRACTS_APPROVE)
            ->get();
    }
}
