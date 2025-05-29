<?php

namespace Packages\Customer\Livewire;

use Livewire\Component;
use Packages\Customer\Models\Customer;
use Livewire\Attributes\Validate;

class CreateCustomer extends Component
{
    public $showModal = false;

    protected $listeners = ['open-create-customer-modal' => 'openModal'];

    #[Validate('required|string|max:255')]
    public $customer_name = '';

    #[Validate('nullable|string|max:255')]
    public $phone_number = '';

    #[Validate('nullable|email|max:255')]
    public $email = '';

    #[Validate('nullable|string')]
    public $address = '';

    #[Validate('required|in:individual,company')]
    public $customer_type = 'individual';

    #[Validate('nullable|in:male,female,other')]
    public $gender = '';

    #[Validate('nullable|date')]
    public $birthday = '';

    #[Validate('nullable|string|max:255')]
    public $customer_group = '';

    public function openModal()
    {
        $this->showModal = true;
        $this->resetForm();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->customer_name = '';
        $this->phone_number = '';
        $this->email = '';
        $this->address = '';
        $this->customer_type = 'individual';
        $this->gender = '';
        $this->birthday = '';
        $this->customer_group = '';
    }

    public function save()
    {
        $this->validate();

        Customer::create([
            'customer_code' => Customer::generateCustomerCode(),
            'customer_name' => $this->customer_name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'address' => $this->address,
            'customer_type' => $this->customer_type,
            'gender' => $this->gender,
            'birthday' => $this->birthday,
            'customer_group' => $this->customer_group,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', 'Customer created successfully!');
        $this->closeModal();
        $this->dispatch('customer-created');
    }

    public function render()
    {
        return view('livewire.create-customer');
    }
}
