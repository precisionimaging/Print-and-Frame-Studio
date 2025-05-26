<?php
namespace PFS;

/**
 * One-time install/uninstall chores.
 */
final class Setup {

    public static function activate() : void {
        flush_rewrite_rules();
    }

    public static function deactivate() : void {
        flush_rewrite_rules();
    }
}
