<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        $service = new TransactionService();
        $items   = $service->all();
        $this->view('dashboard/index', [
            'title'    => 'Dashboard',
            'profits'  => $service->recentByType($items, 'income', 7),
            'expenses' => $service->recentByType($items, 'expense', 7),
            'summary'  => $service->summarize($items),
        ]);
    }
}
