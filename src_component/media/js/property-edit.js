(function() {
    const mainRegionField = document.querySelector('#jform_main_region_id');
    const subRegionField = document.querySelector('#jform_sub_region_id');

    // If the fields don't exist, do nothing.
    if (!mainRegionField || !subRegionField) {
        return;
    }

    let originalSubRegionValue = subRegionField.value;

    function updateSubRegions(callback) {
        const mainRegionId = mainRegionField.value;

        // Clear existing options but keep the first one
        while (subRegionField.options.length > 1) {
            subRegionField.remove(1);
        }

        if (mainRegionId && mainRegionId !== '') {
            const url = `index.php?option=com_bookingmanager&task=subregions.getSubRegions&format=json&main_region_id=${mainRegionId}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.success && data.data) {
                        data.data.forEach(item => {
                            const option = new Option(item.name, item.id);
                            subRegionField.add(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching or parsing sub-regions:', error);
                })
                .finally(() => {
                    if (callback) {
                        callback();
                    }
                });
        } else {
            if (callback) {
                callback();
            }
        }
    }

    mainRegionField.addEventListener('change', function() {
        originalSubRegionValue = '';
        updateSubRegions(() => {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.chosen !== 'undefined') {
                jQuery(subRegionField).trigger('chosen:updated');
            }
        });
    });

    // Initial load, but wrapped in a small timeout to ensure other scripts (like 'chosen') might have initialized.
    setTimeout(function() {
        if (mainRegionField.value) {
            updateSubRegions(() => {
                if (originalSubRegionValue) {
                    subRegionField.value = originalSubRegionValue;
                }
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.chosen !== 'undefined') {
                    jQuery(subRegionField).trigger('chosen:updated');
                }
            });
        }
    }, 100);

})();
