<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Illuminate\Support\Str;

class ManageStores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stores:manage
                            {action : Action to perform (list|create|update|delete|assign-user|remove-user)}
                            {--id= : Store ID for update/delete operations}
                            {--name= : Store name}
                            {--slug= : Store slug}
                            {--description= : Store description}
                            {--email= : Store email}
                            {--phone= : Store phone}
                            {--address= : Store address}
                            {--timezone=UTC : Store timezone}
                            {--currency=USD : Store currency}
                            {--language=en : Store language}
                            {--status=active : Store status (active|inactive|suspended)}
                            {--user-id= : User ID for assign/remove operations}
                            {--role=staff : User role (admin|manager|staff|viewer)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage stores/projects dynamically from command line';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                return $this->listStores();
            case 'create':
                return $this->createStore();
            case 'update':
                return $this->updateStore();
            case 'delete':
                return $this->deleteStore();
            case 'assign-user':
                return $this->assignUser();
            case 'remove-user':
                return $this->removeUser();
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: list, create, update, delete, assign-user, remove-user');
                return 1;
        }
    }

    /**
     * List all stores.
     */
    protected function listStores()
    {
        $stores = Store::all();

        if ($stores->isEmpty()) {
            $this->info('No stores found.');
            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Slug', 'Status', 'Email', 'Phone', 'Created'],
            $stores->map(function ($store) {
                return [
                    $store->id,
                    $store->name,
                    $store->slug,
                    $store->status,
                    $store->email ?? 'N/A',
                    $store->phone ?? 'N/A',
                    $store->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        );

        return 0;
    }

    /**
     * Create a new store.
     */
    protected function createStore()
    {
        $name = $this->option('name') ?? $this->ask('Store name');
        $slug = $this->option('slug') ?? Str::slug($name);

        if (Store::where('slug', $slug)->exists()) {
            $this->error("Store with slug '{$slug}' already exists.");
            return 1;
        }

        $store = Store::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $this->option('description'),
            'email' => $this->option('email'),
            'phone' => $this->option('phone'),
            'address' => $this->option('address'),
            'timezone' => $this->option('timezone'),
            'currency' => $this->option('currency'),
            'language' => $this->option('language'),
            'status' => $this->option('status'),
        ]);

        $this->info("Store '{$store->name}' created successfully with ID: {$store->id}");
        return 0;
    }

    /**
     * Update an existing store.
     */
    protected function updateStore()
    {
        $id = $this->option('id') ?? $this->ask('Store ID');
        $store = Store::find($id);

        if (!$store) {
            $this->error("Store with ID {$id} not found.");
            return 1;
        }

        $updateData = array_filter([
            'name' => $this->option('name'),
            'slug' => $this->option('slug'),
            'description' => $this->option('description'),
            'email' => $this->option('email'),
            'phone' => $this->option('phone'),
            'address' => $this->option('address'),
            'timezone' => $this->option('timezone'),
            'currency' => $this->option('currency'),
            'language' => $this->option('language'),
            'status' => $this->option('status'),
        ], function ($value) {
            return $value !== null;
        });

        if (empty($updateData)) {
            $this->error('No update data provided.');
            return 1;
        }

        $store->update($updateData);
        $this->info("Store '{$store->name}' updated successfully.");
        return 0;
    }

    /**
     * Delete a store.
     */
    protected function deleteStore()
    {
        $id = $this->option('id') ?? $this->ask('Store ID');
        $store = Store::find($id);

        if (!$store) {
            $this->error("Store with ID {$id} not found.");
            return 1;
        }

        if (!$this->confirm("Are you sure you want to delete store '{$store->name}'?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $store->delete();
        $this->info("Store '{$store->name}' deleted successfully.");
        return 0;
    }

    /**
     * Assign user to store.
     */
    protected function assignUser()
    {
        $storeId = $this->option('id') ?? $this->ask('Store ID');
        $userId = $this->option('user-id') ?? $this->ask('User ID');
        $role = $this->option('role');

        $store = Store::find($storeId);
        $user = User::find($userId);

        if (!$store) {
            $this->error("Store with ID {$storeId} not found.");
            return 1;
        }

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        // Check if user is already assigned to this store
        if ($store->users()->where('user_id', $userId)->exists()) {
            $this->error("User '{$user->name}' is already assigned to store '{$store->name}'.");
            return 1;
        }

        $store->users()->attach($userId, [
            'role' => $role,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->info("User '{$user->name}' assigned to store '{$store->name}' with role '{$role}'.");
        return 0;
    }

    /**
     * Remove user from store.
     */
    protected function removeUser()
    {
        $storeId = $this->option('id') ?? $this->ask('Store ID');
        $userId = $this->option('user-id') ?? $this->ask('User ID');

        $store = Store::find($storeId);
        $user = User::find($userId);

        if (!$store) {
            $this->error("Store with ID {$storeId} not found.");
            return 1;
        }

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        if (!$store->users()->where('user_id', $userId)->exists()) {
            $this->error("User '{$user->name}' is not assigned to store '{$store->name}'.");
            return 1;
        }

        $store->users()->detach($userId);
        $this->info("User '{$user->name}' removed from store '{$store->name}'.");
        return 0;
    }
}
