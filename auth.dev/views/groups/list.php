<?php $this->startBlock(); ?>

<?php if (!empty($message)) : ?>
    <div class="alert alert-success alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div id="notification"><?=htmlentities($message)?></div>
    </div>
<?php endif; ?>

<div class="pull-left">
    <a href="/groups/new" class="btn btn-primary">Add Group</a>
    <button class="btn btn-danger" id="group-list-delete" form="group-remove-form">Remove Selected Groups</button>
</div>
<form method="get" action="/groups/" class="form-inline pull-right">
    <div class="form-group">
        <label for="group-list-search-term">Search Name</label>
        <input id="group-list-search-term" name="q" value="<?=htmlentities($term);?>" type="text" class="form-control" placeholder="Start of name">
        <button id="group-list-search" type="submit" class="btn btn-default">Search</button>
    </div>
</form>

<form action="/groups/remove" method="get" id="group-remove-form">
    <table id="group-list" class="table">
        <thead>
        <tr>
            <th style="width: 15px;"></th>
            <th>Name</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($entities as $group) : ?>
            <tr>
                <td><input type="checkbox" name="entities[]" value="<?=htmlentities($group->getName())?>" value="1"></td>
                <td><a href="/groups/detail/<?=htmlentities(urlencode($group->getName()))?>"><?=htmlentities($group->getName())?></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</form>

<nav aria-label="Page navigation">
    <ul class="pagination pull-right">
        <?php if ($currentPage > 2) : ?>
            <li>
                <a href="/groups/?page=<?=htmlentities($currentPage - 1)?>" aria-label="Previous" id="pagination-previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php elseif ($currentPage == 2) : ?>
            <li>
                <a href="/groups/" aria-label="Previous" id="pagination-previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="disabled">
                <span aria-hidden="true">&laquo;</span>
            </li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
            <?php if ($i == $currentPage) : ?>
                <li class="active"><span><?=htmlentities($i)?></span></li>
            <?php elseif ($i == 1) : ?>
                <li><a href="/groups/"><?=htmlentities($i)?></a></li>
            <?php else : ?>
                <li><a href="/groups/?page=<?=htmlentities($i)?>"><?=htmlentities($i)?></a></li>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($currentPage < $totalPages) : ?>
            <li>
                <a href="/groups/?page=<?=htmlentities($currentPage + 1)?>" aria-label="Next" id="pagination-next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="disabled">
                <span aria-hidden="true">&raquo;</span>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<?php $content = $this->endBlock(); ?>

<?php include CONFIG_VIEWS_DIR . "/base-template.php"; ?>
