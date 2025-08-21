<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class BookingmanagerModelRegions extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'name', 'parent_id', 'state', 'ordering'
            );
        }

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select($this->getState(
            'list.select',
            'a.id, a.name, a.parent_id, a.state, a.ordering'
        ))->from($this->getDbo()->quoteName('#__bookingmanager_regions', 'a'));

        return $query;
    }
}
