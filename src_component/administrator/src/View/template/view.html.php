<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

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

        JHtml::_('behavior.formvalidator');
        $this->addToolbar();
        parent::display($tpl);
    }
    
    protected function addToolbar()
    {
        JToolbarHelper::title('Edit Template');
        JToolbarHelper::apply('template.apply');
        JToolbarHelper::save('template.save');
        JToolbarHelper::cancel('template.cancel');
    }
}