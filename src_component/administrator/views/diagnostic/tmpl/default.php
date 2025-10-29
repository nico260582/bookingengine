<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <p><?php echo Text::_('COM_BOOKINGMANAGER_DIAGNOSTIC_DESC'); ?></p>
    <?php
    $db = JFactory::getDbo();
    $tables = $db->setQuery('SHOW TABLES LIKE ' . $db->quote($db->getPrefix() . 'booking%'))->loadColumn();
    ?>
    <h3>Booking Manager Tables</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Table Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $table) : ?>
                <tr>
                    <td><?php echo $table; ?></td>
                    <td><span class="badge badge-success">Present</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
