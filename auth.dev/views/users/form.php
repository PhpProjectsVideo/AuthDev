<?php $this->startBlock(); ?>

<div class="container-fluid">
    <div class="col-md-6">
        <?php if ($validationResults->getValidationErrorsForField('form')) : ?>
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 id="title" class="panel-title">Could not submit</h3>
                </div>
                <div class="panel-body">
                    <p id="message"><?=htmlentities($validationResults->getValidationErrorsForField('form')[0])?></p>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" action="<?=htmlentities($_SERVER['REQUEST_URI'])?>">
            <input type="hidden" name="token" value="<?=htmlentities($token)?>">
            <div class="form-group<?=$validationResults->getValidationErrorsForField('username') ? ' has-error' : ''?>">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?=htmlentities($user->getUsername())?>">
                <?php if ($validationResults->getValidationErrorsForField('username')) : ?>
                    <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('username')[0])?></span>
                <?php endif; ?>
            </div>
            <div class="form-group<?=$validationResults->getValidationErrorsForField('password') ? ' has-error' : ''?>">
                <label for="clear-password">Password</label>
                <input type="password" class="form-control" id="clear-password" name="clear-password" placeholder="Password" value="<?=htmlentities($user->getClearTextPassword())?>">
                <?php if ($validationResults->getValidationErrorsForField('password')) : ?>
                    <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('password')[0])?></span>
                <?php endif; ?>
            </div>
            <div class="form-group<?=$validationResults->getValidationErrorsForField('password') ? ' has-error' : ''?>">
                <input type="password" class="form-control" id="clear-password-confirm" name="clear-password-confirm" placeholder="Confirm Password" value="<?=htmlentities($user->getClearTextPassword())?>">
            </div>
            <div class="form-group<?=$validationResults->getValidationErrorsForField('email') ? ' has-error' : ''?>">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?=htmlentities($user->getEmail())?>">
                <?php if ($validationResults->getValidationErrorsForField('email')) : ?>
                    <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('email')[0])?></span>
                <?php endif; ?>
            </div>
            <div class="form-group<?=$validationResults->getValidationErrorsForField('name') ? ' has-error' : ''?>">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" value="<?=htmlentities($user->getName())?>">
                <?php if ($validationResults->getValidationErrorsForField('name')) : ?>
                    <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('name')[0])?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-default" name="save">Save</button>
        </form>
    </div>
    <div class="col-md-6">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 id="title" class="panel-title">Member Groups</h3>
            </div>
            <div class="panel-body">
                <form>
                    <div class="checkbox">
                        <label><input type="checkbox"> Group 1</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox"> Group 2</label>
                    </div>
                    <button type="submit" class="btn btn-success">Remove from Groups</button>
                </form>
            </div>
        </div>

        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 id="title" class="panel-title">Other Groups</h3>
            </div>
            <div class="panel-body">
                <form>
                    <div class="checkbox">
                        <label><input type="checkbox"> Group 3</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox"> Group 4</label>
                    </div>
                    <button type="submit" class="btn btn-warning">Add to Groups</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
