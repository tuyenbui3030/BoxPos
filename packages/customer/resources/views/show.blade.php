@extends('layouts.app')

@section('title', $customer->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <a href="{{ route('customers.index') }}" 
                   class="text-gray-600 hover:text-gray-900 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $customer->name }}</h1>
                <span class="ml-4 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($customer->status) }}
                </span>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('customers.edit', $customer) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Customer
                </a>
                <form action="{{ route('customers.destroy', $customer) }}" 
                      method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                            onclick="return confirm('Are you sure you want to delete this customer?')">
                        Delete Customer
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Customer Information -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Customer Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Full Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <a href="mailto:{{ $customer->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->email }}
                                </a>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $customer->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400">Not provided</span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($customer->status) }}</p>
                        </div>
                    </div>

                    @if($customer->address)
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $customer->address }}</p>
                        </div>
                    @endif

                    @if($customer->notes)
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-500">Notes</label>
                            <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $customer->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Customer Since</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $customer->created_at->format('M j, Y') }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Last Updated</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $customer->updated_at->format('M j, Y') }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Customer ID</span>
                            <span class="text-sm font-medium text-gray-900">#{{ $customer->id }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    
                    <div class="space-y-2">
                        @if($customer->email)
                            <a href="mailto:{{ $customer->email }}" 
                               class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded">
                                Send Email
                            </a>
                        @endif
                        
                        @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}" 
                               class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded">
                                Call Customer
                            </a>
                        @endif
                        
                        <a href="{{ route('customers.edit', $customer) }}" 
                           class="block w-full text-center bg-blue-100 hover:bg-blue-200 text-blue-800 py-2 px-4 rounded">
                            Edit Information
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
