<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;

class BookingmanagerViewComplex extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $sidebar;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->addToolbar();

        $layout = new FileLayout('sidebar');
        $this->sidebar = $layout->render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $isNew = ($this->item->id == 0);
        ToolbarHelper::title($isNew ? 'New Complex' : 'Edit Complex');

        ToolbarHelper::apply('complex.apply');
        ToolbarHelper::save('complex.save');
        ToolbarHelper::cancel('complex.cancel');
    }
}
