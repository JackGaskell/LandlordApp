<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\CollectionHealthService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected CollectionHealthService $collectionHealth,
    ) {}

    public function __invoke(): View
    {
        return view('dashboard', [
            'health' => $this->collectionHealth->snapshot(auth()->user()),
        ]);
    }
}
