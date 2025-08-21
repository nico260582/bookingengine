<?php
defined('_JEXEC') or die;
?>
<h1>Search Results</h1>

<?php if (empty($this->items)) : ?>
    <p>No properties found matching your criteria.</p>
<?php else : ?>
    <ul>
        <?php foreach ($this->items as $item) : ?>
            <li>
                <h2>
                    <a href="<?php echo JRoute::_('index.php?option=com_content&view=article&id=' . $item->article_id); ?>">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </h2>
                <p>Main Region: <?php echo htmlspecialchars($item->main_region, ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Sub Region: <?php echo htmlspecialchars($item->sub_region, ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Max Guests: <?php echo (int)$item->max_guests; ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
