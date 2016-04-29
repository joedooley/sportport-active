<?php

/**
 * Called on plugin uninstall
 */
if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

/* Call config files */
require(dirname(__FILE__) . '/config/config.php');

/* Delete the record from database */
delete_option(SQ_OPTION);
rrmdir(_SQ_CACHE_DIR_);

/**
 * Remove the icon directory if exists
 * @param string $dir
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        @rmdir($dir);
    }
}
