<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=properties'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="1%">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th>
                        <?php echo JHtml::_('grid.sort', 'Article Title', 'article.title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                    <th width="15%">
                        <?php echo JHtml::_('grid.sort', 'Complex', 'complex.name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                    <th width="10%">
                        <?php echo JHtml::_('grid.sort', 'Max Guests', 'a.max_guests', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                    <th width="5%">
                        <?php echo JHtml::_('grid.sort', 'ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td>
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td>
                            <a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=property.edit&id=' . (int) $item->id); ?>">
                                <?php echo $this->escape($item->article_title); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo $this->escape($item->complex_name) ?: 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo (int) $item->max_guests; ?>
                        </td>
                        <td>
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">
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
