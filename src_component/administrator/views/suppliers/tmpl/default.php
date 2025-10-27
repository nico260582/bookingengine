<?php
defined('_JEXEC') or die;
?>

<div id="j-sidebar-container" class="span2">
    <?php // Sidebar is rendered by the layout ?>
</div>
<div id="j-main-container" class="span10">
    <form action="index.php?option=com_bookingmanager&view=suppliers" method="post" name="adminForm" id="adminForm">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="1%"><?php echo JHtml::_('grid.checkall'); ?></th>
                    <th>Name</th>
                    <th>Abbreviation</th>
                    <th width="5%">Published</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr>
                    <td><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
                    <td><a href="<?php echo JRoute::_('index.php?option=com_bookingmanager&task=supplier.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->name); ?></a></td>
                    <td><?php echo $this->escape($item->abbreviation); ?></td>
                    <td><?php echo JHtml::_('jgrid.published', $item->published, $i, 'suppliers.'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>