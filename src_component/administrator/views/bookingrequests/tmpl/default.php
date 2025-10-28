<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>

<div id="j-main-container" class="span12">
    <form action="index.php?option=com_bookingmanager&view=bookingrequests" method="post" name="adminForm" id="adminForm">
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
            <div class="js-stools-container-filters sidebar-nav">
                <div class="js-stools-container-bar">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="has-hint" title="<?php echo Text::_('COM_BOOKINGMANAGER_SEARCH_IN_TITLE'); ?>" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"/>
                    </div>
                    <div class="btn-group pull-left">
                        <button type="submit" class="btn hasTooltip" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn hasTooltip" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                </div>
                <div class="js-stools-container-list">
                    <select name="filter_status" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_('JOPTION_SELECT_STATUS');?></option>
                        <?php $statuses = ['New', 'Confirmed', 'Cancelled', 'Pending']; ?>
                        <?php foreach ($statuses as $status) : ?>
                            <option value="<?php echo $status; ?>" <?php if ($this->state->get('filter.status') == $status) echo 'selected'; ?>><?php echo $status; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="filter_property_name" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_('Select Property');?></option>
                        <?php if (is_array($this->properties)) : ?>
                            <?php foreach ($this->properties as $property) : ?>
                                <option value="<?php echo $this->escape($property); ?>" <?php if ($this->state->get('filter.property_name') == $property) echo 'selected'; ?>><?php echo $this->escape($property); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <input type="text" name="filter_date_from" id="filter_date_from" value="<?php echo $this->state->get('filter.date_from'); ?>" placeholder="From Date" class="input-small" title="Date From" />
                    <input type="text" name="filter_date_to" id="filter_date_to" value="<?php echo $this->state->get('filter.date_to'); ?>" placeholder="To Date" class="input-small" title="Date To" />
                    <select name="filter_date_type" class="inputbox">
                        <option value="created_at" <?php if($this->state->get('filter.date_type') == 'created_at') echo 'selected'; ?>>Creation Date</option>
                        <option value="start_date" <?php if($this->state->get('filter.date_type') == 'start_date') echo 'selected'; ?>>Check-in</option>
                        <option value="end_date" <?php if($this->state->get('filter.date_type') == 'end_date') echo 'selected'; ?>>Check-out</option>
                    </select>
                </div>
            </div>
        </div>
        <div id="j-main-container" class="span10">
            <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="1%" class="hidden-phone"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'Booking Ref', 'a.booking_ref', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'Client Name', 'a.client_name', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'Property', 'a.property_name', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'Check-in', 'a.start_date', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'Check-out', 'a.end_date', $listDirn, $listOrder); ?></th>
                    <th>Guests</th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'Est. Price', 'a.price_estimate', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'Status', 'a.status', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'Created', 'a.created_at', $listDirn, $listOrder); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="hidden-phone"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                    <td><a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=bookingrequest.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->booking_ref); ?></a></td>
                    <td><?php echo $this->escape($item->client_name); ?></td>
                    <td><?php echo $this->escape($item->property_name); ?></td>
                    <td><?php echo HTMLHelper::_('date', $item->start_date, 'Y-m-d'); ?></td>
                    <td><?php echo HTMLHelper::_('date', $item->end_date, 'Y-m-d'); ?></td>
                    <td><?php echo (int)$item->adults + (int)$item->children; ?></td>
                    <td><?php echo $this->escape($item->price_estimate); ?></td>
                    <td><?php echo $this->escape($item->status); ?></td>
                    <td><?php echo HTMLHelper::_('date', $item->created_at, 'Y-m-d H:i'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="10">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
