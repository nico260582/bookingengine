<?php
defined('_JEXEC') or die;
?>

<div id="j-sidebar-container" class="span2">
    <?php echo JHtmlSidebar::render(); ?>
</div>
<div id="j-main-container" class="span10">
    <form action="index.php?option=com_bookingmanager&view=templates" method="post" name="adminForm" id="adminForm">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="1%"></th>
                    <th>Title</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr>
                    <td><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
                    <td><a href="<?php echo JRoute::_('index.php?option=com_bookingmanager&task=template.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->title); ?></a></td>
                    <td><?php echo $this->escape($item->type); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>