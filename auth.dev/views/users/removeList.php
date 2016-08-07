<?php $this->startBlock(); ?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 id="title" class="panel-title">Remove the following users?</h3>
    </div>
    <div class="panel-body">
        <form action="/users/remove" method="post">
            <input type="hidden" name="originalUrl" value="<?=htmlentities($originalUrl)?>">
            <input type="hidden" name="token" value="<?=htmlentities($token)?>">
            <ul>
                <?php foreach ($users as $user) : ?>
                    <li><?=htmlentities($user->getUsername())?>
                        <input type="hidden" name="users[]" value="<?=htmlentities($user->getUsername())?>">
                    </li>
                <?php endforeach; ?>
            </ul>
            <button class="btn btn-danger" id="confirm">Remove Users</button>
            <a href="<?=htmlentities($originalUrl)?>" class="btn btn-default" id="cancel">Cancel</a>
        </form>
    </div>
</div>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
