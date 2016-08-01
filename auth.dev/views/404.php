<?php $this->startBlock(); ?>

    <div class="panel panel-danger">
        <div class="panel-heading">
            <h1 id="title" class="panel-title"><?= htmlentities($title ?: 'There was a problem'); ?></h1>
        </div>
        <div class="panel-body">
            <p id="message"><?= $message ?: "There was an issue with your request"; ?></p>
        </div>
    </div>
    <p><a class="btn btn-primary btn-lg" href="<?= htmlentities($recommendedUrl ?: '/') ?>"><?= htmlentities($recommendedAction ?: 'Go Home') ?></a></p>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
