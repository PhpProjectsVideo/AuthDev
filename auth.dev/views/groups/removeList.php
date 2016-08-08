<?php $this->startBlock(); ?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 id="title" class="panel-title">Remove the following groups?</h3>
    </div>
    <div class="panel-body">
        <form action="/groups/remove" method="post">
            <input type="hidden" name="originalUrl" value="<?=htmlentities($originalUrl)?>">
            <input type="hidden" name="token" value="<?=htmlentities($token)?>">
            <ul>
                <?php foreach ($groups as $group) : ?>
                    <li><?=htmlentities($group->getName())?>
                        <input type="hidden" name="groups[]" value="<?=htmlentities($group->getName())?>">
                    </li>
                <?php endforeach; ?>
            </ul>
            <button class="btn btn-danger" id="confirm">Remove Groups</button>
            <a href="<?=htmlentities($originalUrl)?>" class="btn btn-default" id="cancel">Cancel</a>
        </form>
    </div>
</div>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
