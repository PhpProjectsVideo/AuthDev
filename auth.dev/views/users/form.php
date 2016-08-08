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
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?=htmlentities($entity->getUsername())?>">
                <?php if ($validationResults->getValidationErrorsForField('username')) : ?>
                    <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('username')[0])?></span>
                <?php endif; ?>
            </div>
            <div class="form-group<?=$validationResults->getValidationErrorsForField('password') ? ' has-error' : ''?>">
                <label for="clear-password">Password</label>
                <input type="password" class="form-control" id="clear-password" name="clear-password" placeholder="Password" value="<?=htmlentities($entity->getClearTextPassword())?>">
                <?php if ($validationResults->getValidationErrorsForField('password')) : ?>
                    <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('password')[0])?></span>
                <?php endif; ?>
            </div>
            <div class="form-group<?=$validationResults->getValidationErrorsForField('password') ? ' has-error' : ''?>">
                <input type="password" class="form-control" id="clear-password-confirm" name="clear-password-confirm" placeholder="Confirm Password" value="<?=htmlentities($entity->getClearTextPassword())?>">
            </div>
            <div class="form-group<?=$validationResults->getValidationErrorsForField('email') ? ' has-error' : ''?>">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?=htmlentities($entity->getEmail())?>">
                <?php if ($validationResults->getValidationErrorsForField('email')) : ?>
                    <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('email')[0])?></span>
                <?php endif; ?>
            </div>
            <div class="form-group<?=$validationResults->getValidationErrorsForField('name') ? ' has-error' : ''?>">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" value="<?=htmlentities($entity->getName())?>">
                <?php if ($validationResults->getValidationErrorsForField('name')) : ?>
                    <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('name')[0])?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-default" name="save">Save</button>
        </form>
    </div>
    <div class="col-md-6">
        <?php if ($message ?? false) : ?>
            <div class="alert alert-<?=htmlentities($messageStatus)?> alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div id="notification"><?=htmlentities($message)?></div>
            </div>
        <?php endif; ?>
        <?php if ($entity->getId()) : ?>
            <div class="panel panel-success" id="member-groups">
                <div class="panel-heading">
                    <h3 id="title" class="panel-title">Member Groups</h3>
                </div>
                <div class="panel-body">
                    <form action="/users/update-groups/<?=htmlentities(urlencode($entity->getUserName()))?>" method="post">
                        <?php
                        foreach ($groups as $group)
                        {
                            if ($entity->isMemberOfGroup($group))
                            {
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="groupIds[]" value="<?=htmlentities($group->getId())?>"> <?=htmlentities($group->getName())?></label>
                                </div>
                        <?php
                                
                            }
                        }
                        ?>
                        <input type="hidden" name="token" value="<?=htmlentities($token)?>">
                        <input type="hidden" name="operation" value="remove">
                        <button type="submit" class="btn btn-success">Remove from Groups</button>
                    </form>
                </div>
            </div>

            <div class="panel panel-warning" id="other-groups">
                <div class="panel-heading">
                    <h3 id="title" class="panel-title">Other Groups</h3>
                </div>
                <div class="panel-body">
                    <form action="/users/update-groups/<?=htmlentities(urlencode($entity->getUserName()))?>" method="post">
                        <?php
                        foreach ($groups as $group)
                        {
                            if (!$entity->isMemberOfGroup($group))
                            {
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="groupIds[]" value="<?=htmlentities($group->getId())?>"> <?=htmlentities($group->getName())?></label>
                                </div>
                                <?php

                            }
                        }
                        ?>
                        <input type="hidden" name="token" value="<?=htmlentities($token)?>">
                        <input type="hidden" name="operation" value="add">
                        <button type="submit" class="btn btn-warning">Add to Groups</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
