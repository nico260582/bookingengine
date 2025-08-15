<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

// Ensure the helper is loaded
require_once JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php';
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=properties'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <?php endif; ?>
    <div id="j-main-container" class="j-main-container <?php if (!empty($this->sidebar)) : ?>span10<?php endif; ?>">
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
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
                    <th width="15%">
                        <?php echo JHtml::_('grid.sort', 'Supplier', 'supplier.name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                    <th width="5%" class="nowrap center">
                        Rate Status
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
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $i => $item) :
                        $rateStatus = BookingmanagerHelper::getRateStatus($item->article_id);
                        ?>
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
                                <?php echo $this->escape($item->supplier_name) ?: 'N/A'; ?>
                            </td>
                            <td class="center">
                                <?php
                                $status = $rateStatus['status'] ?? 'error';
                                $reason = $rateStatus['reason'] ?? 'Error checking status.';
                                switch ($status) {
                                    case 'complete':
                                        echo '<span class="icon-publish" style="color: green;" title="' . $this->escape($reason) . '"></span>';
                                        break;
                                    case 'partial':
                                        echo '<span class="icon-warning" style="color: orange;" title="' . $this->escape($reason) . '"></span>';
                                        break;
                                    case 'empty':
                                        echo '<span class="icon-unpublish" style="color: red;" title="' . $this->escape($reason) . '"></span>';
                                        break;
                                    default:
                                        echo '<span class="icon-question" title="' . $this->escape($reason) . '"></span>';
                                        break;
                                }
                                ?>
                            </td>
                            <td>
                                <?php echo (int) $item->max_guests; ?>
                            </td>
                            <td>
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            No properties found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
