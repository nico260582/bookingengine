<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>

<div class="bookingmanager-client-portal">
    <h1><?php echo Text::_('COM_BOOKINGMANAGER_CLIENT_PORTAL'); ?></h1>

    <?php if ($this->isLoggedIn && $this->request) : ?>
        <?php echo $this->loadTemplate('portal'); ?>
    <?php else : ?>
        <?php echo $this->loadTemplate('login'); ?>
    <?php endif; ?>
</div>