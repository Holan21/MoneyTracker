<?php

$errors ??= [];
$form   ??= ['login' => ''];
?>
<div class="auth-screen">
    <div class="auth-card">
        <h1>Money Tracker</h1>
        <p class="auth-subtitle">Sign in to your workspace</p>
        <?php if (!empty($error)): ?>
            <p class="form-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="<?= htmlspecialchars(Url::to('/login')) ?>" class="stack-form" novalidate>
            <label>Login
                <input type="text" name="login" autocomplete="username" maxlength="32" value="<?= htmlspecialchars($form['login']) ?>" required>
                <?php if (!empty($errors['login'])): ?><span class="field-error"><?= htmlspecialchars($errors['login']) ?></span><?php endif; ?>
            </label>
            <label>Password
                <input type="password" name="password" autocomplete="current-password" required>
                <?php if (!empty($errors['password'])): ?><span class="field-error"><?= htmlspecialchars($errors['password']) ?></span><?php endif; ?>
            </label>
            <button type="submit" class="btn-primary">Log In</button>
        </form>
        <p class="auth-switch">No account? <a href="<?= htmlspecialchars(Url::to('/register')) ?>">Create one</a></p>
    </div>
</div>
