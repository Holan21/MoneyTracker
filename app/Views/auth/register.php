<?php
$errors ??= [];
$form   ??= ['name' => '', 'login' => ''];
?>
<div class="auth-screen">
    <div class="auth-card">
        <h1>Money Tracker</h1>
        <p class="auth-subtitle">Create a read-only member account</p>
        <?php if (!empty($error)): ?>
            <p class="form-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="<?= htmlspecialchars(Url::to('/register')) ?>" class="stack-form" novalidate>
            <label>Name
                <input type="text" name="name" maxlength="100" value="<?= htmlspecialchars($form['name']) ?>" required>
                <?php if (!empty($errors['name'])): ?><span class="field-error"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?>
            </label>
            <label>Login
                <input type="text" name="login" maxlength="32" value="<?= htmlspecialchars($form['login']) ?>" required>
                <?php if (!empty($errors['login'])): ?><span class="field-error"><?= htmlspecialchars($errors['login']) ?></span><?php endif; ?>
            </label>
            <label>Password
                <input type="password" name="password" required>
                <?php if (!empty($errors['password'])): ?><span class="field-error"><?= htmlspecialchars($errors['password']) ?></span><?php endif; ?>
            </label>
            <label>Confirm password
                <input type="password" name="password_confirm" required>
                <?php if (!empty($errors['password_confirm'])): ?><span class="field-error"><?= htmlspecialchars($errors['password_confirm']) ?></span><?php endif; ?>
            </label>
            <button type="submit" class="btn-primary">Register</button>
        </form>
        <p class="auth-switch">Already registered? <a href="<?= htmlspecialchars(Url::to('/login')) ?>">Log in</a></p>
    </div>
</div>
