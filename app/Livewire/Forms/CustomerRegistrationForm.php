<?php

namespace App\Livewire\Forms;

use App\Models\ActiveCustomer;
use App\Services\AuditService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CustomerRegistrationForm extends Form
{
    #[Validate('required|string|max:50')]
    public string $meterSerial = '';

    #[Validate('required|string|max:20')]
    public string $kebele = '';

    #[Validate('required|string|max:100')]
    public string $firstName = '';

    #[Validate('nullable|string|max:100')]
    public ?string $middleName = '';

    #[Validate('nullable|string|max:100')]
    public ?string $lastName = '';

    #[Validate('nullable|string|max:30')]
    public ?string $phoneNumber = '';

    #[Validate('required|string|max:20')]
    public string $meterSize = '1/2"';

    #[Validate('required|integer')]
    public int $meterNum = 0;

    #[Validate('nullable|string|max:50')]
    public ?string $billNum = '';

    #[Validate('required|numeric')]
    public float $startValue = 0.0;

    #[Validate('required|date')]
    public string $soldDate = '';

    #[Validate('required|string|max:50')]
    public string $customerType = 'Dhunfaa';

    #[Validate('required|string|max:50')]
    public string $paymentWay = 'Direct Cash';

    #[Validate('required|string|max:50')]
    public string $customerBranch = 'Eteya';

    #[Validate('required|string|max:50')]
    public string $customerStatus = 'Active';

    #[Validate('nullable|string|max:50')]
    public ?string $readerBlock = '';

    public function store(): ActiveCustomer
    {
        $this->validate();

        $customer = ActiveCustomer::create([
            'meter_serial'    => $this->meterSerial,
            'kebele'          => $this->kebele,
            'first_name'      => $this->firstName,
            'middle_name'     => $this->middleName ?: null,
            'last_name'       => $this->lastName ?: null,
            'phone_number'    => $this->phoneNumber ?: null,
            'meter_size'      => $this->meterSize,
            'meter_num'       => $this->meterNum,
            'bill_num'        => $this->billNum ?: null,
            'start_value'     => $this->startValue,
            'sold_date'       => $this->soldDate,
            'customer_type'   => $this->customerType,
            'payment_way'     => $this->paymentWay,
            'customer_branch' => $this->customerBranch,
            'customer_status' => $this->customerStatus,
            'reader_block'    => $this->readerBlock ?: null,
            'registration_date' => now()->format('Y-m-d H:i:s'),
        ]);

        app(AuditService::class)->logAudit(
            "Registered new customer {$this->meterSerial} ({$this->firstName})",
            auth()->user()?->fullName() ?? 'System'
        );

        return $customer;
    }
}
