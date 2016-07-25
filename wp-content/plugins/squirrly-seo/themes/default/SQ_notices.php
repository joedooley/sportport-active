<?php
if ($type == 'errors_count') {
    /* for the Menu counter */
    ?>
    <span class='awaiting-mod count-<?php echo $message; ?>'>
        <span class='sq_count pending-count'><?php echo $message; ?></span>
    </span>
<?php } elseif ($type == 'sq_error') { ?>
    <style>.sq_nohelp{display: none;}</style>
    <div id="<?php echo $id ?>" class="<?php echo $type; ?> sq_message">
        <p><strong><?php echo $message; ?></strong></p>
    </div>
    <?php
} elseif ($type == 'sq_success') {
    ?>
    <div id="<?php echo $id ?>" class="<?php echo $type; ?> sq_message">
        <p><strong><?php echo $message; ?></strong></p>
    </div>
    <?php
} elseif ($type == 'sq_helpnotice') {
    ?>
    <div id="<?php echo $id ?>" class="<?php echo $type; ?> sq_message sq_helpnotice">
        <p><strong><?php echo $message; ?></strong></p>
    </div>
    <?php
}