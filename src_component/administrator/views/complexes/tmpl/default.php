<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=complexes'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <?php endif; ?>
    <div id="j-main-container" class="j-main-container <?php if (!empty($this->sidebar)) : ?>span10<?php endif; ?>">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="1%">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th width="20%">
                        <?php echo JHtml::_('grid.sort', 'COM_BOOKINGMANAGER_COMPLEX_NAME_LABEL', 'a.name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                    <th>
                        <?php echo JText::_('COM_BOOKINGMANAGER_COMPLEX_DESCRIPTION_LABEL'); ?>
                    </th>
                    <th width="5%">
                        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td>
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td>
                                <a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=complex.edit&id=' . (int) $item->id); ?>">
                                    <?php echo $this->escape($item->name); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo $this->escape($item->description); ?>
                            </td>
                            <td>
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="text-center">
                            No complexes found.
                        </td>
                    </tr>
                <?php endif; ?>
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
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
