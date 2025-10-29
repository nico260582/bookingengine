<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
?>
<?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo JLayoutHelper::render('joomla.searchtools.sidebar', ['view' => $this]); ?>
    </div>
<?php endif; ?>
<div id="j-main-container" class="span10">
    <p><?php echo Text::_('COM_BOOKINGMANAGER_DIAGNOSTIC_DESC'); ?></p>
</div>
