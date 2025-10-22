<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\HTMLHelper;

class BookingmanagerViewTemplate extends HtmlView
{
    protected $form;
    protected $item;
    protected $placeholders;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->placeholders = BookingmanagerHelper::getPlaceholders();

        HTMLHelper::_('behavior.formvalidator');
        $this->addToolbar();
        parent::display($tpl);
    }
    
    protected function addToolbar()
    {
        ToolbarHelper::title('Edit Template');
        ToolbarHelper::apply('template.apply');
        ToolbarHelper::save('template.save');
        ToolbarHelper::cancel('template.cancel');
    }
}