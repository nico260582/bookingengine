<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'complexes'));

HTMLHelper::_('bootstrap.addTab', 'myTab', 'complexes', Text::_('Complexes'));
?>
<div class="tab-pane active" id="complexes">
    <form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=complexes'); ?>" method="post" name="adminForm" id="adminForm">
        <table class="table table-striped" id="complexList">
            <thead>
                <tr>
                    <th width="1%" class="center">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th class="title">
                        Name
                    </th>
                    <th width="1%" class="center">
                        ID
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
                        <a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=complex.edit&id=' . (int) $item->id); ?>">
                            <?php echo $this->escape($item->name); ?>
                        </a>
                    </td>
                    <td class="center">
                        <?php echo (int) $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php echo $this->pagination->getListFooter(); ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<?php
HTMLHelper::_('bootstrap.endTab');

HTMLHelper::_('bootstrap.addTab', 'myTab', 'regions', Text::_('Regions'));
?>
<div class="tab-pane" id="regions">
    <?php echo LayoutHelper::render(
        'regions_list',
        array(
            'items' => $this->regionsItems,
            'pagination' => $this->regionsPagination
        ),
        JPATH_COMPONENT_ADMINISTRATOR . '/views/complexes/tmpl'
    ); ?>
</div>
<?php
HTMLHelper::_('bootstrap.endTab');

HTMLHelper::_('bootstrap.endTabSet');

// Add toolbar buttons dynamically
$active = Factory::getApplication()->input->get('active', 'complexes');
if ($active == 'complexes') {
    ToolbarHelper::addNew('complex.add');
    ToolbarHelper::editList('complex.edit');
    ToolbarHelper::deleteList('Are you sure?', 'complexes.delete');
} elseif ($active == 'regions') {
    ToolbarHelper::addNew('region.add');
    ToolbarHelper::editList('region.edit');
    ToolbarHelper::deleteList('Are you sure?', 'regions.delete');
}
?>
