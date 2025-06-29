<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Packages\Store\Models\Store;

class ProjectService
{
    protected string $defaultProject;

    public function __construct()
    {
        $this->defaultProject = 'boxpos-dashboard';
    }

    /**
     * Get all available projects.
     */
    public function getAllProjects(): Collection
    {
        return Store::active()->get()->map(function ($store) {
            return $this->formatStoreAsProject($store);
        });
    }

    /**
     * Get project by slug.
     */
    public function getProject(string $slug): ?array
    {
        $store = Store::where('slug', $slug)->where('status', 'active')->first();

        return $store ? $this->formatStoreAsProject($store) : null;
    }

    /**
     * Get project by ID.
     */
    public function getProjectById(int $id): ?array
    {
        $store = Store::where('id', $id)->where('status', 'active')->first();

        return $store ? $this->formatStoreAsProject($store) : null;
    }

    /**
     * Get projects accessible by current user.
     */
    public function getUserProjects(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return collect();
        }

        $accessibleStoreIds = $this->getUserAccessibleProjectIds($userId);

        return Store::whereIn('id', $accessibleStoreIds)
            ->where('status', 'active')
            ->get()
            ->map(function ($store) {
                return $this->formatStoreAsProject($store);
            });
    }

    /**
     * Check if user has access to project.
     */
    public function userHasAccess(string $projectSlug, ?int $userId = null): bool
    {
        $project = $this->getProject($projectSlug);

        if (!$project) {
            return false;
        }

        return $this->userHasAccessById($project['id'], $userId);
    }

    /**
     * Check if user has access to project by ID.
     */
    public function userHasAccessById(int $projectId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return false;
        }

        // Cache access check for 5 minutes
        $cacheKey = "user_project_access_{$userId}_{$projectId}";

        return cache()->remember($cacheKey, 300, function () use ($userId, $projectId) {
            return DB::table('user_stores')
                ->where('user_id', $userId)
                ->where('store_id', $projectId)
                ->where('is_active', true)
                ->exists();
        });
    }

    /**
     * Get user's role in a project.
     */
    public function getUserRole(string $projectSlug, ?int $userId = null): ?string
    {
        $project = $this->getProject($projectSlug);

        if (!$project) {
            return null;
        }

        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return null;
        }

        // Cache role check for 5 minutes
        $cacheKey = "user_project_role_{$userId}_{$project['id']}";

        return cache()->remember($cacheKey, 300, function () use ($userId, $project) {
            $userStore = DB::table('user_stores')
                ->where('user_id', $userId)
                ->where('store_id', $project['id'])
                ->where('is_active', true)
                ->first();

            return $userStore?->role ?? 'user';
        });
    }

    /**
     * Get current project for user.
     */
    public function getCurrentProject(): ?array
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        // Get from user's current_store_id
        if ($user->current_store_id) {
            $project = $this->getProjectById($user->current_store_id);

            if ($project && $this->userHasAccess($project['slug'])) {
                return $project;
            }
        }

        // Fallback to default project
        $defaultProject = $this->getProject($this->defaultProject);

        if ($defaultProject && $this->userHasAccess($defaultProject['slug'])) {
            return $defaultProject;
        }

        // Return first accessible project
        $userProjects = $this->getUserProjects();
        return $userProjects->first();
    }

    /**
     * Switch to a project.
     */
    public function switchProject(int $projectId): array
    {
        $project = $this->getProjectById($projectId);

        if (!$project) {
            throw new \Exception("Project with ID {$projectId} not found");
        }

        if (!$this->userHasAccessById($projectId)) {
            throw new \Exception("Access denied to project: {$project['name']}");
        }

        // Update user's current project
        Auth::user()->update(['current_store_id' => $projectId]);

        return $project;
    }

    /**
     * Get default project.
     */
    public function getDefaultProject(): ?array
    {
        return $this->getProject($this->defaultProject);
    }

    /**
     * Get accessible project IDs for user.
     */
    protected function getUserAccessibleProjectIds(int $userId): array
    {
        // Cache accessible projects for 5 minutes
        $cacheKey = "user_accessible_projects_{$userId}";

        return cache()->remember($cacheKey, 300, function () use ($userId) {
            return DB::table('user_stores')
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->pluck('store_id')
                ->toArray();
        });
    }

    /**
     * Format store model as project array for backward compatibility.
     */
    protected function formatStoreAsProject(Store $store): array
    {
        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'description' => $store->description ?? 'No description',
            'icon' => $this->getStoreIcon($store),
            'color' => $this->getStoreColor($store),
            'domain' => $store->domain,
            'status' => $store->status,
            'settings' => $store->settings ?? [],
            'timezone' => $store->timezone,
            'currency' => $store->currency,
            'language' => $store->language,
        ];
    }

    /**
     * Get icon for store based on name or settings.
     */
    protected function getStoreIcon(Store $store): string
    {
        // Check if icon is stored in settings
        if (isset($store->settings['icon'])) {
            return $store->settings['icon'];
        }

        // Default icons based on store name/type
        $name = strtolower($store->name);

        if (str_contains($name, 'dashboard') || str_contains($name, 'admin')) {
            return '📊';
        }

        if (str_contains($name, 'coffee') || str_contains($name, 'cafe')) {
            return '☕';
        }

        if (str_contains($name, 'inventory') || str_contains($name, 'warehouse')) {
            return '📦';
        }

        if (str_contains($name, 'retail') || str_contains($name, 'shop')) {
            return '🏪';
        }

        return '🏢'; // Default business icon
    }

    /**
     * Get color for store based on settings or default.
     */
    protected function getStoreColor(Store $store): string
    {
        // Check if color is stored in settings
        if (isset($store->settings['color'])) {
            return $store->settings['color'];
        }

        // Default colors based on store type
        $name = strtolower($store->name);

        if (str_contains($name, 'dashboard') || str_contains($name, 'admin')) {
            return '#066fd1'; // Blue
        }

        if (str_contains($name, 'coffee') || str_contains($name, 'cafe')) {
            return '#8b4513'; // Brown
        }

        if (str_contains($name, 'inventory') || str_contains($name, 'warehouse')) {
            return '#28a745'; // Green
        }

        return '#6c757d'; // Default gray
    }
}
