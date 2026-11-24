













<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; Restaurant DataBase</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>frontend/assets/css/style.css">
</head>
<body class="login-body">

<div class="login-card">
     
    <h1>Zam Zam <br> Restaurant</h1>
    <!-- <p class="subtitle">Restaurant Management System</p> -->

    <?php if ($error): ?>
    <div class="alert alert-danger">❌ <?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group" style="text-align:left;">
            <label for="username">Username</label>
            <input type="text"
                   id="username"
                   name="username"
                   class="form-control"
                   placeholder="Enter your username"
                   value="<?= h(post('username')) ?>"
                   required
                   autofocus>
        </div>

        <div class="form-group" style="text-align:left;">
            <label for="password">Password</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   placeholder="Enter your password"
                   required>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="margin-top:8px;">
            <!-- 🔐 Sign In -->
             login
        </button>
    </form>

    <!-- <p style="margin-top:20px;font-size:12px;color:var(--muted);">
        Use the username and password from the APP_USER table.
    </p> -->
</div>

</body>
</html>