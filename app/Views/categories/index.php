<?php
?>
<div class="container">
    <div class="page-heading">
        <h2>Categories</h2>
        <?php if ($isAdmin): ?>
            <a class="btn-primary" href="<?= htmlspecialchars(Url::to('/categories/create')) ?>">Add category</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($error)): ?>
        <p class="form-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form class="filter-bar" method="get" action="<?= htmlspecialchars(Url::to('/categories')) ?>">
        <input type="text" name="q" placeholder="Search name" value="<?= htmlspecialchars($filters['q']) ?>">
        <select name="type">
            <option value="">All types</option>
            <option value="income"  <?= $filters['type'] === 'income'  ? 'selected' : '' ?>>Income</option>
            <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
        </select>
        <button class="btn-logout" type="submit">Filter</button>
    </form>

    <div class="history-list">
        <?php if (!$items): ?>
            <p class="empty-hint">No categories match the filter</p>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <article class="history-row">
                <span class="icon-category <?= $item['type'] === 'income' ? 'icon-income' : 'icon-expense' ?>">
                    <?= htmlspecialchars(mb_substr($item['name'], 0, 1)) ?>
                </span>
                <div class="history-main">
                    <a href="<?= htmlspecialchars(Url::to('/categories/' . $item['id'])) ?>"><?= htmlspecialchars($item['name']) ?></a>
                    <small><?= htmlspecialchars($item['type']) ?> · <?= htmlspecialchars($item['description'] ?: 'No description') ?></small>
                </div>
                <?php if ($isAdmin): ?>
                    <div class="row-actions">
                        <a href="<?= htmlspecialchars(Url::to('/categories/' . $item['id'] . '/edit')) ?>">Edit</a>
                        <form method="post" action="<?= htmlspecialchars(Url::to('/categories/' . $item['id'] . '/delete')) ?>" onsubmit="return confirm('Delete category and its transactions?');">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</div>
