<?php
?>
<div class="container">
    <div class="page-heading">
        <h2><?= htmlspecialchars($item['name']) ?></h2>
        <?php if ($isAdmin): ?>
            <a class="btn-primary" href="<?= htmlspecialchars(Url::to('/transactions/create')) ?>">Add transaction</a>
        <?php endif; ?>
    </div>
    <p class="summary-line"><?= htmlspecialchars($item['type']) ?> · <?= htmlspecialchars($item['description'] ?: 'No description') ?></p>

    <div class="history-list">
        <?php if (!$transactions): ?>
            <p class="empty-hint">This category has no transactions yet</p>
        <?php endif; ?>
        <?php foreach ($transactions as $row): ?>
            <article class="history-row">
                <div class="history-main">
                    <a href="<?= htmlspecialchars(Url::to('/transactions/' . $row['id'])) ?>"><?= htmlspecialchars($row['title']) ?></a>
                    <small><?= htmlspecialchars(date('d.m.Y H:i', strtotime($row['occurred_at']))) ?></small>
                </div>
                <strong class="<?= $row['type'] === 'income' ? 'amount-profit' : 'amount-expense' ?>">
                    <?= htmlspecialchars(number_format((float)$row['amount'], 2)) ?>$
                </strong>
            </article>
        <?php endforeach; ?>
    </div>
</div>
