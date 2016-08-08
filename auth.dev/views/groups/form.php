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
            <div class="panel panel-success" id="member-permissions">
                <div class="panel-heading">
                    <h3 id="title" class="panel-title">Owned Permissions</h3>
                </div>
                <div class="panel-body">
                    <form action="/groups/update-permissions/<?=htmlentities(urlencode($entity->getName()))?>" method="post">
                        <?php
                        foreach ($permissions as $permission)
                        {
                            if ($entity->isOwnerofPermission($permission))
                            {
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="permissionIds[]" value="<?=htmlentities($permission->getId())?>"> <?=htmlentities($permission->getName())?></label>
                                </div>
                                <?php

                            }
                        }
                        ?>
                        <input type="hidden" name="token" value="<?=htmlentities($token)?>">
                        <input type="hidden" name="operation" value="remove">
                        <button type="submit" class="btn btn-success">Remove from Permissions</button>
                    </form>
                </div>
            </div>

            <div class="panel panel-warning" id="other-permissions">
                <div class="panel-heading">
                    <h3 id="title" class="panel-title">Other Permissions</h3>
                </div>
                <div class="panel-body">
                    <form action="/groups/update-permissions/<?=htmlentities(urlencode($entity->getName()))?>" method="post">
                        <?php
                        foreach ($permissions as $permission)
                        {
                            if (!$entity->isOwnerofPermission($permission))
                            {
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="permissionIds[]" value="<?=htmlentities($permission->getId())?>"> <?=htmlentities($permission->getName())?></label>
                                </div>
                                <?php

                            }
                        }
                        ?>
                        <input type="hidden" name="token" value="<?=htmlentities($token)?>">
                        <input type="hidden" name="operation" value="add">
                        <button type="submit" class="btn btn-warning">Add to Permissions</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
