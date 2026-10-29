<?php
?>
<div class="container">
    <div class="page-heading">
        <div>
            <h2>Transactions</h2>
            <p class="summary-line">Personal finance history with filtering and role-based access.</p>
        </div>
        <?php if ($isAdmin): ?>
            <a class="btn-primary" href="<?= htmlspecialchars(Url::to('/transactions/create')) ?>">Add transaction</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($error)): ?>
        <p class="form-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form class="filter-bar" method="get" action="<?= htmlspecialchars(Url::to('/transactions')) ?>">
        <input type="text" name="q" placeholder="Search title" value="<?= htmlspecialchars($filters['q']) ?>">
        <select name="type">
            <option value="">All types</option>
            <option value="income"  <?= $filters['type'] === 'income'  ? 'selected' : '' ?>>Income</option>
            <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
        </select>
        <select name="category_id">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['id'] ?>" <?= (string)$filters['category_id'] === (string)$category['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" step="0.01" name="min_amount" placeholder="Min" value="<?= htmlspecialchars($filters['min_amount']) ?>">
        <input type="number" step="0.01" name="max_amount" placeholder="Max" value="<?= htmlspecialchars($filters['max_amount']) ?>">
        <button class="btn-logout" type="submit">Filter</button>
    </form>

    <div class="stats-grid">
        <article class="stat-card">
            <span>Income</span>
            <strong class="amount-profit"><?= htmlspecialchars(number_format((float)$summary['income'], 2)) ?>$</strong>
        </article>
        <article class="stat-card">
            <span>Expense</span>
            <strong class="amount-expense"><?= htmlspecialchars(number_format((float)$summary['expense'], 2)) ?>$</strong>
        </article>
        <article class="stat-card">
            <span>Balance</span>
            <strong><?= htmlspecialchars(number_format((float)$summary['balance'], 2)) ?>$</strong>
        </article>
    </div>

    <div class="history-list">
        <?php if (!$items): ?>
            <p class="empty-hint">No transactions match the filter</p>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <article class="history-row">
                <span class="icon-category <?= $item['type'] === 'income' ? 'icon-income' : 'icon-expense' ?>">
                    <?= htmlspecialchars(mb_substr($item['category_name'], 0, 1)) ?>
                </span>
                <div class="history-main">
                    <a href="<?= htmlspecialchars(Url::to('/transactions/' . $item['id'])) ?>"><?= htmlspecialchars($item['title']) ?></a>
                    <small><?= htmlspecialchars($item['category_name']) ?> · <?= htmlspecialchars(date('d.m.Y H:i', strtotime($item['occurred_at']))) ?></small>
                </div>
                <strong class="<?= $item['type'] === 'income' ? 'amount-profit' : 'amount-expense' ?>">
                    <?= $item['type'] === 'income' ? '+' : '-' ?><?= htmlspecialchars(number_format((float)$item['amount'], 2)) ?>$
                </strong>
                <?php if (!empty($item['receipt'])): ?>
                    <span class="badge">file</span>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                    <div class="row-actions">
                        <a href="<?= htmlspecialchars(Url::to('/transactions/' . $item['id'] . '/edit')) ?>">Edit</a>
                        <form method="post" action="<?= htmlspecialchars(Url::to('/transactions/' . $item['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this transaction?');">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</div>
