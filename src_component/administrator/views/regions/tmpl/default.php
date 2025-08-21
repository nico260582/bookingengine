<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.multiselect');
?>
<form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=regions'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
    <div id="j-main-container">
    <?php endif; ?>
        <table class="table table-striped" id="regionList">
            <thead>
                <tr>
                    <th width="1%" class="center">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th class="title">
                        <?php echo Text::_('Name'); ?>
                    </th>
                    <th width="5%" class="center">
                        <?php echo Text::_('State'); ?>
                    </th>
                    <th width="10%" class="center">
                        <?php echo Text::_('Ordering'); ?>
                    </th>
                    <th width="1%" class="center">
                        <?php echo Text::_('ID'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="center">
                        <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td>
                        <a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=region.edit&id=' . (int) $item->id); ?>">
                            <?php echo $this->escape($item->name); ?>
                        </a>
                    </td>
                    <td class="center">
                        <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'regions.', true, 'cb'); ?>
                    </td>
                    <td class="center">
                        <?php echo $item->ordering; ?>
                    </td>
                    <td class="center">
                        <?php echo (int) $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php echo $this->pagination->getListFooter(); ?>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
