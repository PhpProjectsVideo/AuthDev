<?php $this->startBlock(); ?>

<?php if (!empty($message)) : ?>
    <div class="alert alert-success alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div id="notification"><?=htmlentities($message)?></div>
    </div>
<?php endif; ?>

<div class="pull-left">
    <a href="/users/new" class="btn btn-primary">Add User</a>
</div>
<form method="get" action="/users/" class="form-inline pull-right">
    <div class="form-group">
        <label for="user-list-search-term">Search Username</label>
        <input id="user-list-search-term" name="q" value="<?=htmlentities($term);?>" type="text" class="form-control" placeholder="Start of username">
        <button id="user-list-search" type="submit" class="btn btn-default">Search</button>
    </div>
</form>
<table id="user-list" class="table">
    <thead>
    <tr>
        <th style="width: 15px;"></th>
        <th>Username</th>
        <th>Name</th>
        <th>Email</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user) : ?>
        <tr>
            <td></td>
            <td><a href="/users/detail/<?=htmlentities(urlencode($user->getUsername()))?>"><?=htmlentities($user->getUsername())?></a></td>
            <td><?=htmlentities($user->getName())?></td>
            <td><?=htmlentities($user->getEmail())?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<nav aria-label="Page navigation">
    <ul class="pagination pull-right">
        <?php if ($currentPage > 2) : ?>
            <li>
                <a href="/users/?page=<?=htmlentities($currentPage - 1)?>" aria-label="Previous" id="pagination-previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php elseif ($currentPage == 2) : ?>
            <li>
                <a href="/users/" aria-label="Previous" id="pagination-previous">
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
                <li><a href="/users/"><?=htmlentities($i)?></a></li>
            <?php else : ?>
                <li><a href="/users/?page=<?=htmlentities($i)?>"><?=htmlentities($i)?></a></li>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($currentPage < $totalPages) : ?>
            <li>
                <a href="/users/?page=<?=htmlentities($currentPage + 1)?>" aria-label="Next" id="pagination-next">
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
