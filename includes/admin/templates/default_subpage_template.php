<h1>
    <?php echo $page_title ?>
</h1>
<div class="ridpool_admin_content">
    <?php echo ($page_search ? sprintf('<form method="get">%s</form>',$page_search) : ''); ?>
<?php echo $page_content ?>
</div>
