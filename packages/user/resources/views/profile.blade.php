@extends('layouts.app')

@section('title', 'User Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">User Profile</h1>
            <div class="flex space-x-3">
                <button id="edit-profile-btn" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Profile
                </button>
                <button id="change-password-btn" 
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Change Password
                </button>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Information -->
            <div class="lg:col-span-2">
                <!-- Profile Details Card -->
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Full Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $user->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email Verified</label>
                            <p class="mt-1 text-sm">
                                @if($user->email_verified_at)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Verified
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Not Verified
                                    </span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Account Status</label>
                            <p class="mt-1 text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form (Hidden by default) -->
                <div id="edit-profile-form" class="bg-white shadow-md rounded-lg p-6 mb-6 hidden">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Edit Profile</h2>
                    
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <button type="button" 
                                    id="cancel-edit-btn"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Form (Hidden by default) -->
                <div id="change-password-form" class="bg-white shadow-md rounded-lg p-6 mb-6 hidden">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Change Password</h2>
                    
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Current Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    New Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       required>
                                <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters long</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm New Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <button type="button" 
                                    id="cancel-password-btn"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Details</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Member Since</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $user->created_at->format('M j, Y') }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Last Updated</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $user->updated_at->format('M j, Y') }}
                            </span>
                        </div>
                        
                        @if($user->email_verified_at)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Email Verified</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $user->email_verified_at->format('M j, Y') }}
                            </span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">User ID</span>
                            <span class="text-sm font-medium text-gray-900">#{{ $user->id }}</span>
                        </div>
                    </div>
                </div>

                @if(!$user->email_verified_at)
                <div class="bg-yellow-50 shadow-md rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Verify Email</h3>
                    <p class="text-sm text-yellow-700 mb-4">
                        Please verify your email address to access all features.
                    </p>
                    <form action="{{ route('verification.send') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                            Resend Verification Email
                        </button>
                    </form>
                </div>
                @endif

                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Actions</h3>
                    
                    <div class="space-y-2">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded">
                                Sign Out
                            </button>
                        </form>
                        
                        <button type="button" 
                                class="block w-full text-center bg-red-100 hover:bg-red-200 text-red-800 py-2 px-4 rounded">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('edit-profile-btn');
    const passwordBtn = document.getElementById('change-password-btn');
    const editForm = document.getElementById('edit-profile-form');
    const passwordForm = document.getElementById('change-password-form');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const cancelPasswordBtn = document.getElementById('cancel-password-btn');

    editBtn.addEventListener('click', function() {
        editForm.classList.remove('hidden');
        passwordForm.classList.add('hidden');
    });

    passwordBtn.addEventListener('click', function() {
        passwordForm.classList.remove('hidden');
        editForm.classList.add('hidden');
    });

    cancelEditBtn.addEventListener('click', function() {
        editForm.classList.add('hidden');
    });

    cancelPasswordBtn.addEventListener('click', function() {
        passwordForm.classList.add('hidden');
    });
});
</script>
@endsection
