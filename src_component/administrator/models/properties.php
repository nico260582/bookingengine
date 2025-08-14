<?php
defined('_JEXEC') or die;

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
                'complex_name', 'complex.name',
                'supplier_abbreviation', 'supplier.abbreviation',
                'published', 'a.published'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'article.title', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.*, article.title AS article_title, complex.name AS complex_name, supplier.abbreviation AS supplier_abbreviation'
            )
        )
            ->from($db->quoteName('#__bookingmanager_properties', 'a'))
            // This LEFT JOIN now includes all properties, showing supplier where available
            ->join('LEFT', $db->quoteName('#__bookingmanager_property_map', 'supplier_map') . ' ON a.article_id = supplier_map.property_id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_suppliers', 'supplier') . ' ON supplier_map.supplier_id = supplier.id')
            // These joins get the article title and complex name
            ->join('LEFT', $db->quoteName('#__content', 'article') . ' ON a.article_id = article.id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_complex_property_map', 'map') . ' ON a.id = map.property_id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_complexes', 'complex') . ' ON map.complex_id = complex.id');


        // Add sorting
        $orderCol = $this->state->get('list.ordering', 'article.title');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
