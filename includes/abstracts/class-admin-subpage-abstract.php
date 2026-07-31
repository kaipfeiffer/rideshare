<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Backwards-compatible alias for list-table admin subpages.
 *
 * New list-table subpages should extend Admin_List_Subpage_Abstract.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0
 */
abstract class Admin_Subpage_Abstract extends Admin_List_Subpage_Abstract
{
}
