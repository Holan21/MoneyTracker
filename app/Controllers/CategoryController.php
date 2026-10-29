<?php

declare(strict_types=1);

class CategoryController extends Controller
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        $service = new CategoryService();
        $error   = null;
        $filters = [
            'q'    => trim((string)($_GET['q'] ?? '')),
            'type' => (string)($_GET['type'] ?? ''),
        ];
        try {
            $items = $service->filter($service->all(), $_GET);
        } catch (ValidationException $e) {
            $error = $e->getMessage();
            $items = [];
        }
        $this->view('categories/index', [
            'title'   => 'Categories',
            'items'   => $items,
            'filters' => $filters,
            'error'   => $error,
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
            (new CategoryService())->create($_POST);
            $this->redirect('/categories');
        } catch (ValidationException $e) {
            $this->renderForm(null, $e->getMessage(), $_POST, $e->getErrors());
        }
    }

    public function show(array $params = []): void
    {
        Auth::requireLogin();
        $id         = $this->param($params, 'id');
        $catService = new CategoryService();
        $txService  = new TransactionService();
        $item       = $catService->find($id);
        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => '404', 'layout' => 'auth']);
            return;
        }
        $this->view('categories/show', [
            'title'        => $item['name'],
            'item'         => $item,
            'transactions' => $txService->byCategory($id),
        ]);
    }

    public function edit(array $params = []): void
    {
        Auth::requireAdmin();
        $item = (new CategoryService())->find($this->param($params, 'id'));
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
        $service = new CategoryService();
        $item    = $service->find($this->param($params, 'id'));
        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => '404', 'layout' => 'auth']);
            return;
        }
        try {
            $service->update((int)$item['id'], $_POST);
            $this->redirect('/categories');
        } catch (ValidationException $e) {
            $this->renderForm($item, $e->getMessage(), $_POST, $e->getErrors());
        }
    }

    public function destroy(array $params = []): void
    {
        Auth::requireAdmin();
        (new CategoryService())->delete($this->param($params, 'id'));
        $this->redirect('/categories');
    }

    private function renderForm(?array $item, ?string $error = null, array $old = [], array $errors = []): void
    {
        $this->view('categories/form', [
            'title'  => $item ? 'Edit category' : 'New category',
            'item'   => $item,
            'error'  => $error,
            'errors' => $errors,
            'form'   => [
                'name'        => $old['name']        ?? ($item['name']        ?? ''),
                'type'        => $old['type']        ?? ($item['type']        ?? 'expense'),
                'description' => $old['description'] ?? ($item['description'] ?? ''),
            ],
            'action' => $item ? Url::to('/categories/' . $item['id']) : Url::to('/categories'),
        ]);
    }
}
