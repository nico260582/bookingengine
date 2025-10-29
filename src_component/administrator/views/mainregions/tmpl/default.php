<?php
defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <form action="index.php?option=com_bookingmanager&view=mainregions" method="post" name="adminForm" id="adminForm">
        <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="1%"><?php echo JHtml::_('grid.checkall'); ?></th>
                    <th><?php echo JHtml::_('searchtools.sort', 'Name', 'a.name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                    <th width="10%"><?php echo JHtml::_('searchtools.sort', 'Published', 'a.published', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                    <th width="1%"><?php echo JHtml::_('searchtools.sort', 'ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
                        <td><a href="<?php echo JRoute::_('index.php?option=com_bookingmanager&task=mainregion.edit&id=' . $item->id); ?>"><?php echo $this->escape($item->name); ?></a></td>
                        <td class="center"><?php echo JHtml::_('jgrid.published', $item->published, $i, 'mainregions.', true, 'cb'); ?></td>
                        <td class="center"><?php echo $item->id; ?></td>
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
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
