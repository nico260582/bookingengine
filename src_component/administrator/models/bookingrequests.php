<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;

class BookingmanagerModelBookingrequests extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'status', 'a.status',
                'property_name', 'a.property_name',
                'client_name', 'a.client_name',
                'created_at', 'a.created_at',
            ];
        }
        parent::__construct($config);
    }

    protected function populateState($ordering = 'created_at', $direction = 'DESC')
    {
        $app = Factory::getApplication();
        $this->setState('filter.search', $app->input->get('filter_search'));
        $this->setState('filter.status', $app->input->get('filter_status', '', 'string'));
        $this->setState('filter.property_name', $app->input->get('filter_property_name', '', 'string'));
        $this->setState('filter.date_from', $app->input->get('filter_date_from', '', 'string'));
        $this->setState('filter.date_to', $app->input->get('filter_date_to', '', 'string'));
        $this->setState('filter.date_type', $app->input->get('filter_date_type', 'created_at', 'string'));

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select($this->getStoreId('list.select', 'a.*'))
              ->from($db->quoteName('#__booking_requests', 'a'));

        // Search filter
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.client_name LIKE ' . $search . ' OR a.client_email LIKE ' . $search . ' OR a.booking_ref LIKE ' . $search . ')');
        }

        // Status filter
        $status = $this->getState('filter.status');
        if (!empty($status)) {
            $query->where('a.status = ' . $db->quote($status));
        }

        // Property name filter
        $propertyName = $this->getState('filter.property_name');
        if (!empty($propertyName)) {
            $query->where('a.property_name = ' . $db->quote($propertyName));
        }

        // Date range filter
        $dateFrom = $this->getState('filter.date_from');
        $dateTo = $this->getState('filter.date_to');
        $dateType = $this->getState('filter.date_type');

        if (!empty($dateFrom) && !empty($dateTo) && in_array($dateType, ['created_at', 'start_date', 'end_date'])) {
            $dateField = 'a.' . $dateType;
            $query->where($db->quoteName($dateField) . ' BETWEEN ' . $db->quote($dateFrom . ' 00:00:00') . ' AND ' . $db->quote($dateTo . ' 23:59:59'));
        }

        // Sorting
        $listOrder = $this->getState('list.ordering', 'created_at');
        $listDirn = $this->getState('list.direction', 'DESC');
        $query->order($db->escape($listOrder) . ' ' . $db->escape($listDirn));

        return $query;
    }

    public function getProperties()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('property_name'))
            ->from($db->quoteName('#__booking_requests'))
            ->group($db->quoteName('property_name'))
            ->order($db->quoteName('property_name'));
        $db->setQuery($query);
        return $db->loadColumn();
    }
}