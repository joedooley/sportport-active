<?php
# Engage LCache object caching system.
# We use a 'require_once()' here because in PHP 5.5+ changes to symlinks
# are not detected by the opcode cache, making it frustrating to deploy.
#
# More info: http://codinghobo.com/opcache-and-symlink-based-deployments/
#

$lcache_path = dirname( realpath( __FILE__ ) ) . '/plugins/wp-lcache/object-cache.php';
require_once( $lcache_path );