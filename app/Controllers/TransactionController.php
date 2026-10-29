<?php

declare(strict_types=1);

class TransactionController extends Controller
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        $txService  = new TransactionService();
        $catService = new CategoryService();
        $filters    = [
            'q'           => trim((string)($_GET['q'] ?? '')),
            'type'        => (string)($_GET['type'] ?? ''),
            'category_id' => (string)($_GET['category_id'] ?? ''),
            'min_amount'  => (string)($_GET['min_amount'] ?? ''),
            'max_amount'  => (string)($_GET['max_amount'] ?? ''),
        ];
        $all   = $txService->all();
        $error = null;
        try {
            $items = $txService->filter($all, $_GET);
        } catch (ValidationException $e) {
            $error = $e->getMessage();
            $items = [];
        }
        $this->view('transactions/index', [
            'title'      => 'Transactions',
            'items'      => $items,
            'filters'    => $filters,
            'categories' => $catService->all(),
            'summary'    => $txService->summarize($items),
            'error'      => $error,
        ]);
    }

    public function create(array $params = []): void
    {
        Auth::requireAdmin();
        $this->renderForm(null);
    }

    public function store(array $params = []): void
    {
        Auth::requireAdmin();
        try {
            (new TransactionService())->create($_POST, $_FILES['receipt'] ?? []);
            $this->redirect('/transactions');
        } catch (ValidationException $e) {
            $this->renderForm(null, $e->getMessage(), $_POST, $e->getErrors());
        }
    }

    public function show(array $params = []): void
    {
        Auth::requireLogin();
        $item = (new TransactionService())->find($this->param($params, 'id'));
        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => '404', 'layout' => 'auth']);
            return;
        }
        $this->view('transactions/show', ['title' => $item['title'], 'item' => $item]);
    }

    public function edit(array $params = []): void
    {
        Auth::requireAdmin();
        $item = (new TransactionService())->find($this->param($params, 'id'));
        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => '404', 'layout' => 'auth']);
            return;
        }
        $this->renderForm($item);
    }

    public function update(array $params = []): void
    {
        Auth::requireAdmin();
        $service = new TransactionService();
        $item    = $service->find($this->param($params, 'id'));
        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => '404', 'layout' => 'auth']);
            return;
        }
        try {
            $service->update($item, $_POST, $_FILES['receipt'] ?? []);
            $this->redirect('/transactions');
        } catch (ValidationException $e) {
            $this->renderForm($item, $e->getMessage(), $_POST, $e->getErrors());
        }
    }

    public function destroy(array $params = []): void
    {
        Auth::requireAdmin();
        (new TransactionService())->delete($this->param($params, 'id'));
        $this->redirect('/transactions');
    }

    private function renderForm(?array $item, ?string $error = null, array $old = [], array $errors = []): void
    {
        $occurred = $old['occurred_at'] ?? ($item['occurred_at'] ?? date('Y-m-d\TH:i'));
        if (str_contains((string)$occurred, ' ') && !str_contains((string)$occurred, 'T')) {
            $occurred = date('Y-m-d\TH:i', strtotime((string)$occurred));
        }

        $this->view('transactions/form', [
            'title'      => $item ? 'Edit transaction' : 'New transaction',
            'item'       => $item,
            'error'      => $error,
            'errors'     => $errors,
            'form'       => [
                'title'       => $old['title']       ?? ($item['title']       ?? ''),
                'amount'      => $old['amount']       ?? ($item['amount']      ?? ''),
                'type'        => $old['type']         ?? ($item['type']        ?? 'expense'),
                'category_id' => (string)($old['category_id'] ?? ($item['category_id'] ?? '')),
                'occurred_at' => $occurred,
            ],
            'categories' => (new CategoryService())->all(),
            'action'     => $item ? Url::to('/transactions/' . $item['id']) : Url::to('/transactions'),
        ]);
    }
}
