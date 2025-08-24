<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class BookingmanagerViewSubregion extends BaseHtmlView
{
    protected $form;
    protected $item;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $isNew = ($this->item->id == 0);
        ToolbarHelper::title($isNew ? Text::_('COM_BOOKINGMANAGER_SUB_REGION_NEW') : Text::_('COM_BOOKINGMANAGER_SUB_REGION_EDIT'));

        ToolbarHelper::apply('subregion.apply');
        ToolbarHelper::save('subregion.save');
        ToolbarHelper::cancel('subregion.cancel');
    }
}
