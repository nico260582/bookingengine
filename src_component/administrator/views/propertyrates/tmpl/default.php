<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
?>
<form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=propertyrates'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo JLayoutHelper::render('joomla.searchtools.sidebar', ['view' => $this]); ?>
        </div>
    <?php endif; ?>
    <div id="j-main-container" class="span10">
        <div class="form-horizontal">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group">
                        <div class="control-label">
                            <label for="filter_property_id"><?php echo Text::_('COM_BOOKINGMANAGER_PROPERTYRATES_FILTER_PROPERTY'); ?></label>
                        </div>
                        <div class="controls">
                            <select name="filter_property_id" id="filter_property_id" onchange="this.form.submit()">
                                <option value="0"><?php echo Text::_('COM_BOOKINGMANAGER_PROPERTYRATES_SELECT_PROPERTY'); ?></option>
                                <?php foreach ($this->properties as $property) : ?>
                                    <option value="<?php echo (int) $property->id; ?>" <?php echo ($this->selectedPropertyId == $property->id) ? 'selected="selected"' : ''; ?>>
                                        <?php echo $this->escape($property->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($this->selectedPropertyId) : ?>
            <?php if (!empty($this->rateData->error)) : ?>
                <div class="alert alert-error">
                    <h4><?php echo Text::_('COM_BOOKINGMANAGER_PROPERTYRATES_ERROR_HEADING'); ?></h4>
                    <p><?php echo $this->escape($this->rateData->error); ?></p>
                </div>
            <?php else : ?>
                <!-- Rate editing form will be here -->
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
