<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=properties'); ?>" method="post" name="adminForm" id="adminForm">
        <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="1%"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'Name', 'a.name', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'Complex', 'complex_name', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'Supplier', 'supplier_name', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'Main Region', 'main_region_name', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'Sub Region', 'sub_region_name', $listDirn, $listOrder); ?></th>
                    <th>Rate Status</th>
                    <th width="10%"><?php echo HTMLHelper::_('searchtools.sort', 'Published', 'a.published', $listDirn, $listOrder); ?></th>
                    <th width="1%"><?php echo HTMLHelper::_('searchtools.sort', 'ID', 'a.id', $listDirn, $listOrder); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                        <td><a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=property.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->name); ?></a></td>
                        <td><?php echo $this->escape($item->complex_name); ?></td>
                        <td><?php echo $this->escape($item->supplier_name); ?></td>
                        <td><?php echo $this->escape($item->main_region_name); ?></td>
                        <td><?php echo $this->escape($item->sub_region_name); ?></td>
                        <td>
                            <?php
                            $rateStatus = BookingmanagerHelper::getRateStatus($item->article_id);
                            $statusClass = '';
                            if ($rateStatus['status'] == 'complete') {
                                $statusClass = 'badge badge-success';
                            } elseif ($rateStatus['status'] == 'partial') {
                                $statusClass = 'badge badge-warning';
                            } else {
                                $statusClass = 'badge badge-important';
                            }
                            ?>
                            <span class="<?php echo $statusClass; ?>" title="<?php echo $this->escape($rateStatus['reason']); ?>">
                                <?php echo ucfirst($rateStatus['status']); ?>
                            </span>
                        </td>
                        <td class="center"><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'properties.', true, 'cb'); ?></td>
                        <td class="center"><?php echo (int) $item->id; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="9">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
