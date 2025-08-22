<?php
namespace Rtholidays\Component\Bookingmanager\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class RegionsModel extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
                'parent_id', 'a.parent_id',
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.name', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select($this->getState('list.select', 'a.*'))
            ->from($db->quoteName('#__bookingmanager_regions', 'a'));

        // Add sorting
        $orderCol = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    public function delete(&$pks)
    {
        $db = $this->getDbo();
        $pks = (array) $pks;
        $table = $this->getTable('Region');

        foreach ($pks as $pk) {
            // Get all children of this region
            $query = $db->getQuery(true)
                ->select('id')
                ->from($table->getTableName())
                ->where('parent_id = ' . (int) $pk);
            $children = $db->setQuery($query)->loadColumn();

            if (!empty($children)) {
                // Recursively delete children
                if (!$this->delete($children)) {
                    return false;
                }
            }

            // Unassign properties from this region
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bookingmanager_properties'))
                ->set($db->quoteName('main_region_id') . ' = NULL')
                ->where($db->quoteName('main_region_id') . ' = ' . (int) $pk);
            $db->setQuery($query)->execute();

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bookingmanager_properties'))
                ->set($db->quoteName('sub_region_id') . ' = NULL')
                ->where($db->quoteName('sub_region_id') . ' = ' . (int) $pk);
            $db->setQuery($query)->execute();
        }

        // Use the table's delete method to remove the regions
        if (!$table->delete($pks)) {
            $this->setError($table->getError());
            return false;
        }

        return true;
    }
}
