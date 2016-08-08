<?php $this->startBlock(); ?>

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
    <div class="form-permission<?=$validationResults->getValidationErrorsForField('name') ? ' has-error' : ''?>">
        <label for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" value="<?=htmlentities($entity->getName())?>">
        <?php if ($validationResults->getValidationErrorsForField('name')) : ?>
            <span class="help-block"><?=htmlentities($validationResults->getValidationErrorsForField('name')[0])?></span>
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-default" name="save">Save</button>
</form>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
