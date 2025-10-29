<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

class BookingmanagerModelProperties extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'article_title', 'article.title',
                'max_guests', 'a.max_guests',
                'number_of_units', 'a.number_of_units',
                'complex_name', 'complex.name',
                'supplier_name', 'supplier.name',
                'published', 'a.published',
                'main_region_name', 'main_region.name',
                'sub_region_name', 'sub_region.name'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     * This final version includes a check to prevent deprecated warnings.
     */
    protected function populateState($ordering = 'article.title', $direction = 'asc')
    {
        // Get the application instance.
        $app = Factory::getApplication('administrator');

        // Set a default ordering if none is found in the user state.
        // This prevents the "explode(): Passing null to parameter #2" warning.
        $fullOrdering = $app->getUserStateFromRequest($this->context . '.list.fullordering', 'list_fullordering');
        if (empty($fullOrdering)) {
            $app->setUserState($this->context . '.list.fullordering', $ordering . ' ' . $direction);
        }

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.*, article.title AS name, complex.name AS complex_name, supplier.name AS supplier_name, supplier.rules, main_region.name AS main_region_name, sub_region.name AS sub_region_name'
            )
        )
            ->from($db->quoteName('#__bookingmanager_properties', 'a'))
            ->join('LEFT', $db->quoteName('#__content', 'article') . ' ON a.article_id = article.id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_complex_property_map', 'map') . ' ON a.id = map.property_id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_complexes', 'complex') . ' ON map.complex_id = complex.id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_property_map', 'supplier_map') . ' ON a.article_id = supplier_map.property_id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_suppliers', 'supplier') . ' ON supplier_map.supplier_id = supplier.id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_main_regions', 'main_region') . ' ON a.main_region_id = main_region.id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_sub_regions', 'sub_region') . ' ON a.sub_region_id = sub_region.id');

        // Filter by search in title or supplier name
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(article.title LIKE ' . $like . ' OR supplier.name LIKE ' . $like . ')');
        }

        // Add sorting
        $orderCol = $this->state->get('list.ordering', 'article.title');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();

        if (empty($items)) {
            return [];
        }

        $processedItems = [];
        foreach ($items as $item) {
            // If we haven't seen this property ID yet, add it to our list.
            if (!isset($processedItems[$item->id])) {
                $processedItems[$item->id] = $item;
            } else {
                // If we have seen this ID, it's a duplicate because of a complex join.
                // Let's append the complex name to the existing entry.
                if (!empty($item->complex_name) && strpos($processedItems[$item->id]->complex_name, $item->complex_name) === false) {
                    $processedItems[$item->id]->complex_name .= ', ' . $item->complex_name;
                }
            }
        }

        return array_values($processedItems);
    }
}
