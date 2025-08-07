<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewPropertyrates extends HtmlView
{
    protected $properties;
    protected $rateData;
    protected $selectedPropertyId;
    
    public function display($tpl = null)
    {
        JHtml::_('formbehavior.chosen', 'select');
        $model = $this->getModel();
        $app = Factory::getApplication();

        $this->properties = $model->getPropertiesForFilter();
        $this->selectedPropertyId = $app->input->getInt('filter_property_id', 0);

        if ($this->selectedPropertyId) {
            $this->rateData = $model->getRateData($this->selectedPropertyId);
        }

        BookingmanagerHelper::addSubmenu('propertyrates');
        $this->addToolbar();
        parent::display($tpl);
    }
    
    protected function addToolbar()
    {
        JToolbarHelper::title('Property Rates');
        if ($this->selectedPropertyId && empty($this->rateData->error)) {
            JToolbarHelper::save('propertyrates.save');
        }
        JToolbarHelper::preferences('com_bookingmanager');
    }
}