<?php
?>
<div class="container narrow">
    <article class="panel detail-card">
        <h2><?= htmlspecialchars($item['title']) ?></h2>
        <p class="<?= $item['type'] === 'income' ? 'amount-profit' : 'amount-expense' ?>">
            <?= htmlspecialchars(number_format((float)$item['amount'], 2)) ?>$
        </p>
        <p>Category: <a href="<?= htmlspecialchars(Url::to('/categories/' . $item['category_id'])) ?>"><?= htmlspecialchars($item['category_name']) ?></a></p>
        <p>Type: <?= htmlspecialchars($item['type']) ?></p>
        <p>Date: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($item['occurred_at']))) ?></p>
        <?php if (!empty($item['receipt'])): ?>
            <p>Receipt: <a href="<?= htmlspecialchars(Url::to('/uploads/' . $item['receipt'])) ?>"><?= htmlspecialchars($item['receipt']) ?></a></p>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <div class="row-actions">
                <a class="btn-logout" href="<?= htmlspecialchars(Url::to('/transactions/' . $item['id'] . '/edit')) ?>">Edit</a>
                <form method="post" action="<?= htmlspecialchars(Url::to('/transactions/' . $item['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this transaction?');">
                    <button class="btn-logout" type="submit">Delete</button>
                </form>
            </div>
        <?php endif; ?>
    </article>
</div>
