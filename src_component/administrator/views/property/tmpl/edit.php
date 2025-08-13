<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="form-horizontal">
        <div class="row-fluid">
            <div class="span9">
                <div class="form-vertical">
                    <?php echo $this->form->getControlGroup('article_id'); ?>
                    <?php echo $this->form->getControlGroup('max_guests'); ?>
                    <?php echo $this->form->getControlGroup('complex_id'); ?>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
