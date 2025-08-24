<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
?>
<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="form-horizontal">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_BOOKINGMANAGER_SUB_REGION_DETAILS')); ?>
            <div class="row-fluid">
                <div class="span9">
                    <fieldset class="form-vertical">
                        <?php echo $this->form->renderFieldset('details'); ?>
                    </fieldset>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
