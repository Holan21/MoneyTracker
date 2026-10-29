<?php
$errors ??= [];
?>
<div class="container narrow">
    <h2><?= htmlspecialchars($item ? 'Edit transaction' : 'New transaction') ?></h2>
    <?php if ($error): ?>
        <p class="form-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="stack-form panel" enctype="multipart/form-data" novalidate>
        <label>Title
            <input type="text" name="title" maxlength="200" value="<?= htmlspecialchars($form['title']) ?>" required>
            <?php if (!empty($errors['title'])): ?><span class="field-error"><?= htmlspecialchars($errors['title']) ?></span><?php endif; ?>
        </label>
        <label>Amount
            <input type="number" step="0.01" min="0.01" name="amount" value="<?= htmlspecialchars((string)$form['amount']) ?>" required>
            <?php if (!empty($errors['amount'])): ?><span class="field-error"><?= htmlspecialchars($errors['amount']) ?></span><?php endif; ?>
        </label>
        <label>Type
            <select name="type">
                <option value="income"  <?= $form['type'] === 'income'  ? 'selected' : '' ?>>Income</option>
                <option value="expense" <?= $form['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
            </select>
            <?php if (!empty($errors['type'])): ?><span class="field-error"><?= htmlspecialchars($errors['type']) ?></span><?php endif; ?>
        </label>
        <label>Category
            <select name="category_id" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id'] ?>" <?= $form['category_id'] === (string)$category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['category_id'])): ?><span class="field-error"><?= htmlspecialchars($errors['category_id']) ?></span><?php endif; ?>
        </label>
        <label>Date and time
            <input type="datetime-local" name="occurred_at" value="<?= htmlspecialchars($form['occurred_at']) ?>">
            <?php if (!empty($errors['occurred_at'])): ?><span class="field-error"><?= htmlspecialchars($errors['occurred_at']) ?></span><?php endif; ?>
        </label>
        <label>Receipt (jpg, png, pdf)
            <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
        </label>
        <button class="btn-primary" type="submit">Save</button>
    </form>
</div>
