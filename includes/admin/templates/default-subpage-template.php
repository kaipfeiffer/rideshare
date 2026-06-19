<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $page_title ?>
    </h1>

    <a href="<?php echo $create_new_link;?>" class="page-title-action"><?php echo($create_new_label) ?></a>
    <div class="ridepool_admin_content">
        <?php echo ($page_search ? sprintf('<form method="get">%s</form>',$page_search) : ''); ?>
    <?php echo $page_content ?>
    </div>
</div>
