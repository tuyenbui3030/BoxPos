<?php

namespace Packages\Customer\Livewire;

use Packages\Customer\Models\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class CustomerTable extends PowerGridComponent
{
    public string $tableName = 'customer-table-gmf9py-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Customer::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('customer_code')
            ->add('customer_name')
            ->add('phone_number')
            ->add('email')
            ->add('address')
            ->add('customer_type')
            ->add('gender')
            ->add('birthday_formatted', fn (Customer $model) => Carbon::parse($model->birthday)->format('d/m/Y'))
            ->add('customer_group')
            ->add('current_debt')
            ->add('total_sales')
            ->add('total_sales_minus_returns')
            ->add('last_transaction_at')
            ->add('created_by')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Customer code', 'customer_code')
                ->sortable()
                ->searchable(),

            Column::make('Customer name', 'customer_name')
                ->sortable()
                ->searchable(),

            Column::make('Phone number', 'phone_number')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Address', 'address')
                ->sortable()
                ->searchable(),

            Column::make('Customer type', 'customer_type')
                ->sortable()
                ->searchable(),

            Column::make('Gender', 'gender')
                ->sortable()
                ->searchable(),

            Column::make('Birthday', 'birthday_formatted', 'birthday')
                ->sortable(),

            Column::make('Customer group', 'customer_group')
                ->sortable()
                ->searchable(),

            Column::make('Current debt', 'current_debt')
                ->sortable()
                ->searchable(),

            Column::make('Total sales', 'total_sales')
                ->sortable()
                ->searchable(),

            Column::make('Total sales minus returns', 'total_sales_minus_returns')
                ->sortable()
                ->searchable(),

            Column::make('Last transaction at', 'last_transaction_at_formatted', 'last_transaction_at')
                ->sortable(),

            Column::make('Last transaction at', 'last_transaction_at')
                ->sortable()
                ->searchable(),

            Column::make('Created by', 'created_by'),
            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datepicker('birthday'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(Customer $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id])
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
