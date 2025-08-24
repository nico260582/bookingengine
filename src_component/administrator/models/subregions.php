<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class BookingmanagerModelSubregions extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
                'published', 'a.published',
                'main_region_name', 'mr.name'
            );
        }

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.*, mr.name AS main_region_name')
            ->from($db->quoteName('#__bookingmanager_sub_regions', 'a'))
            ->join('LEFT', $db->quoteName('#__bookingmanager_main_regions', 'mr') . ' ON (' . $db->quoteName('a.main_region_id') . ' = ' . $db->quoteName('mr.id') . ')');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.published = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(a.published IN (0, 1))');
        }

        // Filter by search in name
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.name LIKE ' . $like . ' OR mr.name LIKE ' . $like . ')');
        }

        // Add the ordering clause
        $orderCol  = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
