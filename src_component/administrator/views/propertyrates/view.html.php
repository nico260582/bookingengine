<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;

class BookingmanagerViewPropertyrates extends HtmlView
{
    protected $properties;
    protected $rateData;
    protected $selectedPropertyId;
    public $sidebar;
    
    public function display($tpl = null)
    {
        HTMLHelper::_('formbehavior.chosen', 'select');
        $model = $this->getModel();
        $app = Factory::getApplication();

        $this->properties = $model->getPropertiesForFilter();
        $this->selectedPropertyId = $app->input->getInt('filter_property_id', 0);

        if ($this->selectedPropertyId) {
            $this->rateData = $model->getRateData($this->selectedPropertyId);
        }

        $this->addToolbar();

        $layout = new FileLayout('joomla.searchtools.default', ['view' => $this]);
        $this->sidebar = $layout->render();

        parent::display($tpl);
    }
    
    protected function addToolbar()
    {
        ToolbarHelper::title('Property Rates');
        if ($this->selectedPropertyId && empty($this->rateData->error)) {
            ToolbarHelper::save('propertyrates.save');
        }
        ToolbarHelper::preferences('com_bookingmanager');
    }
}
