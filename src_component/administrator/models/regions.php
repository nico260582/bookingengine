<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;

class BookingmanagerModelRegions extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
                'published', 'a.published',
            );
        }

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.*')
            ->from($db->quoteName('#__bookingmanager_main_regions', 'a'));

        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.published = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(a.published IN (0, 1))');
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('a.name LIKE ' . $like);
        }

        $orderCol  = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        if (empty($items)) {
            return [];
        }

        $db = $this->getDbo();
        $mainRegionIds = array_map(function($item) { return (int) $item->id; }, $items);

        if (empty($mainRegionIds)) {
            return $items;
        }

        $query = $db->getQuery(true);
        $query->select('id, sub_region_name AS name, published, main_region_id')
              ->from($db->quoteName('#__bookingmanager_sub_regions'))
              ->where('main_region_id IN (' . implode(',', $mainRegionIds) . ')')
              ->order('name ASC');

        $subRegions = $db->setQuery($query)->loadObjectList();

        $groupedSubRegions = [];
        foreach ($subRegions as $sub) {
            $groupedSubRegions[$sub->main_region_id][] = $sub;
        }

        $result = [];
        foreach ($items as $item) {
            $item->level = 1;
            $item->task_type = 'mainregion';
            $result[] = $item;

            if (isset($groupedSubRegions[$item->id])) {
                foreach ($groupedSubRegions[$item->id] as $sub) {
                    $sub->level = 2;
                    $sub->task_type = 'subregion';
                    $sub->id = $item->id . '.' . $sub->id;
                    $result[] = $sub;
                }
            }
        }

        return $result;
    }

    public function publish(&$pks, $value = 1)
    {
        return $this->processBatchAction($pks, 'publish', $value);
    }

    public function unpublish(&$pks, $value = 0)
    {
        return $this->processBatchAction($pks, 'publish', $value);
    }

    public function delete(&$pks)
    {
        return $this->processBatchAction($pks, 'delete');
    }

    private function processBatchAction($pks, $action, $value = null)
    {
        $mainRegionPks = [];
        $subRegionPks = [];

        foreach ($pks as $pk) {
            if (strpos($pk, '.') !== false) {
                list(, $subId) = explode('.', $pk);
                $subRegionPks[] = (int) $subId;
            } else {
                $mainRegionPks[] = (int) $pk;
            }
        }

        $db = $this->getDbo();

        try {
            if ($action === 'delete') {
                if (!empty($mainRegionPks)) {
                    $query = $db->getQuery(true)
                        ->delete($db->quoteName('#__bookingmanager_main_regions'))
                        ->where('id IN (' . implode(',', $mainRegionPks) . ')');
                    $db->setQuery($query)->execute();
                }
                if (!empty($subRegionPks)) {
                    $query = $db->getQuery(true)
                        ->delete($db->quoteName('#__bookingmanager_sub_regions'))
                        ->where('id IN (' . implode(',', $subRegionPks) . ')');
                    $db->setQuery($query)->execute();
                }
            } else { // publish or unpublish
                if (!empty($mainRegionPks)) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__bookingmanager_main_regions'))
                        ->set($db->quoteName('published') . ' = ' . (int) $value)
                        ->where('id IN (' . implode(',', $mainRegionPks) . ')');
                    $db->setQuery($query)->execute();
                }
                if (!empty($subRegionPks)) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__bookingmanager_sub_regions'))
                        ->set($db->quoteName('published') . ' = ' . (int) $value)
                        ->where('id IN (' . implode(',', $subRegionPks) . ')');
                    $db->setQuery($query)->execute();
                }
            }
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
}
