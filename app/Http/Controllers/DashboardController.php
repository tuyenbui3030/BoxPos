<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProjectService;

class DashboardController extends Controller
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        $currentProject = $this->projectService->getCurrentProject();

        if (!$currentProject) {
            // Redirect to first available project
            $userProjects = $this->projectService->getUserProjects();
            if ($userProjects->isNotEmpty()) {
                $firstProject = $userProjects->first();
                $this->projectService->switchProject($firstProject['id']);
                return redirect()->route('locale.dashboard', ['locale' => app()->getLocale()]);
            }

            return view('dashboard', ['currentProject' => null]);
        }

        // Redirect to specific dashboard based on project dashboard_route
        if (isset($currentProject['dashboard_route'])) {
            return redirect()->route($currentProject['dashboard_route'], ['locale' => app()->getLocale()]);
        }

        // Default dashboard
        return view('dashboard', compact('currentProject'));
    }

    /**
     * Show business dashboard.
     */
    public function business()
    {
        $currentProject = $this->projectService->getCurrentProject();

        if (!$currentProject || $currentProject['slug'] !== 'boxpos-dashboard') {
            return redirect()->route('locale.dashboard', ['locale' => app()->getLocale()]);
        }

        return view('dashboard-business', compact('currentProject'));
    }

    /**
     * Show coffee shop dashboard.
     */
    public function coffee()
    {
        $currentProject = $this->projectService->getCurrentProject();

        if (!$currentProject || $currentProject['slug'] !== 'coffee-bean-inventory') {
            return redirect()->route('locale.dashboard', ['locale' => app()->getLocale()]);
        }

        return view('dashboard-coffee', compact('currentProject'));
    }
}
