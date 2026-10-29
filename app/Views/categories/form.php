<?php
$errors ??= [];
?>
<div class="container narrow">
    <h2><?= htmlspecialchars($item ? 'Edit category' : 'New category') ?></h2>
    <?php if ($error): ?>
        <p class="form-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="stack-form panel" novalidate>
        <label>Name
            <input type="text" name="name" maxlength="120" value="<?= htmlspecialchars($form['name']) ?>" required>
            <?php if (!empty($errors['name'])): ?><span class="field-error"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?>
        </label>
        <label>Type
            <select name="type">
                <option value="income"  <?= $form['type'] === 'income'  ? 'selected' : '' ?>>Income</option>
                <option value="expense" <?= $form['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
            </select>
            <?php if (!empty($errors['type'])): ?><span class="field-error"><?= htmlspecialchars($errors['type']) ?></span><?php endif; ?>
        </label>
        <label>Description
            <textarea name="description" rows="4" maxlength="500"><?= htmlspecialchars($form['description']) ?></textarea>
            <?php if (!empty($errors['description'])): ?><span class="field-error"><?= htmlspecialchars($errors['description']) ?></span><?php endif; ?>
        </label>
        <button class="btn-primary" type="submit">Save</button>
    </form>
</div>
