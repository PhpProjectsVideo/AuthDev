<?php $this->startBlock(); ?>

<div class="container-fluid">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3 id="title" class="panel-title">Permissions Error</h3>
        </div>
        <div class="panel-body">
            <p id="message">You do not have permission to access this content.</p>
        </div>
    </div>
</div>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
