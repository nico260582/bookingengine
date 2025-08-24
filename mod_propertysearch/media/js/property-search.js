document.addEventListener('DOMContentLoaded', function() {
    const mainRegionField = document.querySelector('#main_region_id');
    const subRegionField = document.querySelector('#sub_region_id');

    if (!mainRegionField || !subRegionField) {
        return;
    }

    function updateSubRegions() {
        const mainRegionId = mainRegionField.value;

        // Clear existing options but keep the first one
        while (subRegionField.options.length > 1) {
            subRegionField.remove(1);
        }

        if (mainRegionId) {
            const url = `index.php?option=com_bookingmanager&task=subregions.getSubRegions&format=json&main_region_id=${mainRegionId}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        data.data.forEach(item => {
                            const option = new Option(item.name, item.id);
                            subRegionField.add(option);
                        });
                    }
                });
        }
    }

    mainRegionField.addEventListener('change', function() {
        updateSubRegions();
    });
});
