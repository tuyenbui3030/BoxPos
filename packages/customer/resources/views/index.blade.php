@extends('layouts.app')

@section('title', __('app.customers'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Debug info -->
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
        <strong>Debug Info:</strong><br>
        Current Locale: {{ app()->getLocale() }}<br>
        Session Locale: {{ session('app_locale', 'none') }}<br>
        URL Locale: {{ request()->route('locale', 'none') }}<br>
        Translation Test: {{ __('app.customers') }}<br>
        Raw Translation: {{ trans('app.customers') }}
    </div>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.customers') }}</h1>
        <a href="{{ route('customers.create') }}"
           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            {{ __('app.create') }} {{ __('app.customers') }}
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if($customers->isEmpty())
            <div class="p-6 text-center text-gray-500">
                <p class="text-lg">{{ __('app.no_data') }}</p>
                <p class="mt-2">
                    <a href="{{ route('customers.create') }}" class="text-blue-500 hover:text-blue-700">
                        {{ __('app.create') }} {{ __('app.customers') }}
                    </a>
                </p>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('app.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('app.email') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('app.phone') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('app.status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('app.date') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('app.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $customer->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $customer->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('app.view') }}</a>
                                <a href="{{ route('customers.edit', $customer) }}"
                                   class="text-blue-600 hover:text-blue-900 mr-3">{{ __('app.edit') }}</a>
                                <form action="{{ route('customers.destroy', $customer) }}"
                                      method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('{{ __('app.confirm_delete') }}')">
                                        {{ __('app.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($customers instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="px-6 py-3 bg-gray-50">
                    {{ $customers->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
