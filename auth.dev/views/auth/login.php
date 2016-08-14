<?php $this->startBlock(); ?>

<div class="container-fluid">
    <h1>Login</h1>
    <?php if ($validationResults->getValidationErrorsForField('login')) : ?>
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 id="title" class="panel-title">Authentication Error</h3>
            </div>
            <div class="panel-body">
                <p id="message"><?=htmlentities($validationResults->getValidationErrorsForField('login')[0])?></p>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="<?=htmlentities($_SERVER['REQUEST_URI'])?>">
        <input type="hidden" name="token" value="<?=htmlentities($token)?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
        </div>

        <input type="hidden" name="originalUrl" value="<?=htmlentities($originalUrl);?>">
        <button type="submit" class="btn btn-default" name="login">Login</button>
    </form>
</div>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
