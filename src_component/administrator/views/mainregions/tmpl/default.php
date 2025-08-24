<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=mainregions'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="1%">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th class="nowrap">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_BOOKINGMANAGER_MAIN_REGIONS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap center">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
                    </th>
                    <th width="5%" class="nowrap center">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
                            <a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=mainregion.edit&id=' . (int) $item->id); ?>">
                                <?php echo $this->escape($item->name); ?>
                            </a>
                        </td>
                        <td class="center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'mainregions.', true, 'cb'); ?>
                        </td>
                        <td class="center">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
