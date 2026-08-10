<?php

namespace App\Livewire\Forms;

use App\Models\ReadingCorrection;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ReadingCorrectionForm extends Form
{
    #[Validate('required|string|max:50')]
    public string $customerCode = '';

    #[Validate('required|string|max:10')]
    public string $readingYear = '';

    #[Validate('required|string|max:20')]
    public string $readingMonth = '';

    #[Validate('required|string|max:50')]
    public string $complainDateTime = '';

    public function store(): ReadingCorrection
    {
        $this->validate();

        $user = auth()->user();
        $department = $user ? $user->fullName() : 'Customer Service';

        return ReadingCorrection::create([
            'customer_code'       => $this->customerCode,
            'reading_year'        => $this->readingYear,
            'reading_month'       => $this->readingMonth,
            'complain_date_time'  => $this->complainDateTime,
            'sending_department' => $department,
            'correction_status'  => 'Pending',
            'new_reading'         => 'NotInserted',
            'approved_name'       => 'Pending',
            'sync_status'         => 'New',
        ]);
    }
}
