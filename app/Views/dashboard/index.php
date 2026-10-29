<?php
$share = (float)$summary['income_share'];
?>
<div class="container dashboard-layout">
    <div class="dashboard-column">
        <h2 class="column-title title-profit">Recent Profit</h2>
        <div class="transaction-list">
            <?php if (!$profits): ?>
                <p class="empty-hint">No income yet</p>
            <?php endif; ?>
            <?php foreach ($profits as $item): ?>
                <a class="transaction-card" href="<?= htmlspecialchars(Url::to('/transactions/' . $item['id'])) ?>">
                    <span class="icon-category icon-income" aria-hidden="true"><?= htmlspecialchars(mb_substr($item['category_name'] ?? 'I', 0, 1)) ?></span>
                    <span class="amount amount-profit"><?= htmlspecialchars(number_format((float)$item['amount'], 0, '.', '')) ?>$</span>
                    <span class="date"><?= htmlspecialchars(date('d.m.Y', strtotime($item['occurred_at']))) ?><br><?= htmlspecialchars(date('H:i', strtotime($item['occurred_at']))) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="dashboard-column column-center">
        <div class="chart-wrapper">
            <div class="chart-ring" style="--income-share: <?= htmlspecialchars((string)$share) ?>%"></div>
            <div class="chart-value"><?= htmlspecialchars(number_format((float)$summary['balance'], 0, '.', '')) ?>$</div>
        </div>
        <p class="chart-caption">Balance</p>
    </div>

    <div class="dashboard-column">
        <h2 class="column-title title-expense">Recent Expenses</h2>
        <div class="transaction-list">
            <?php if (!$expenses): ?>
                <p class="empty-hint">No expenses yet</p>
            <?php endif; ?>
            <?php foreach ($expenses as $item): ?>
                <a class="transaction-card" href="<?= htmlspecialchars(Url::to('/transactions/' . $item['id'])) ?>">
                    <span class="icon-category icon-expense" aria-hidden="true"><?= htmlspecialchars(mb_substr($item['category_name'] ?? 'E', 0, 1)) ?></span>
                    <span class="amount amount-expense"><?= htmlspecialchars(number_format((float)$item['amount'], 0, '.', '')) ?>$</span>
                    <span class="date"><?= htmlspecialchars(date('d.m.Y', strtotime($item['occurred_at']))) ?><br><?= htmlspecialchars(date('H:i', strtotime($item['occurred_at']))) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
