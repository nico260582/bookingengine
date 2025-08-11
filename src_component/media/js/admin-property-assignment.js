document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.property-assignment-ajax-container');

    containers.forEach(container => {
        const supplierId = container.dataset.supplierId;
        const fieldId = container.dataset.fieldId;
        const fieldName = container.dataset.fieldName;

        const searchInput = container.querySelector(`#${fieldId}_search`);
        const resultsContainer = container.querySelector(`#${fieldId}_results .property-list-results`);
        const selectedContainer = container.querySelector(`#${fieldId}_selected .property-list-selected`);
        const hiddenSelect = document.querySelector(`#${fieldId}_hidden_select`);
        const selectedPlaceholder = container.querySelector(`#${fieldId}_selected_placeholder`);

        // Debounce function to limit AJAX calls
        const debounce = (func, delay) => {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        };

        const search = async (searchTerm) => {
            resultsContainer.innerHTML = '<div class="property-item-placeholder">Searching...</div>';

            const url = `index.php?option=com_bookingmanager&task=properties.get&format=raw&supplier_id=${supplierId}&search=${encodeURIComponent(searchTerm)}`;

            try {
                const response = await fetch(url);
                const json = await response.json();

                if (json.success && json.data) {
                    renderResults(json.data);
                } else {
                    resultsContainer.innerHTML = '<div class="property-item-placeholder">Error loading results.</div>';
                }
            } catch (error) {
                console.error('Error fetching properties:', error);
                resultsContainer.innerHTML = '<div class="property-item-placeholder">Error fetching properties.</div>';
            }
        };

        const renderResults = (properties) => {
            resultsContainer.innerHTML = '';
            const existingSelectedIds = Array.from(hiddenSelect.options).map(opt => opt.value);

            if (Object.keys(properties).length === 0) {
                resultsContainer.innerHTML = '<div class="property-item-placeholder">No properties found.</div>';
                return;
            }

            for (const id in properties) {
                const property = properties[id];

                if (existingSelectedIds.includes(property.id)) {
                    continue;
                }

                const item = document.createElement('div');
                item.classList.add('property-item');
                item.dataset.id = property.id;
                item.dataset.title = property.title;

                let content = `<span>${property.title}</span>`;
                if (property.assignment && !property.assignment.is_current) {
                    item.classList.add('assigned-other');
                    item.title = `Assigned to ${property.assignment.abbreviation}`;
                    content += `<span class="supplier-abbr">(${property.assignment.abbreviation})</span>`;
                } else {
                    item.addEventListener('click', () => addSelectedItem(property));
                }

                item.innerHTML = content;
                resultsContainer.appendChild(item);
            }
        };

        const addSelectedItem = (property) => {
            if (selectedPlaceholder) {
                selectedPlaceholder.style.display = 'none';
            }

            const item = document.createElement('div');
            item.classList.add('property-item');
            item.dataset.id = property.id;
            item.innerHTML = `<span>${property.title}</span><button type="button" class="btn btn-mini btn-danger remove-property">X</button>`;
            selectedContainer.appendChild(item);
            item.querySelector('.remove-property').addEventListener('click', (e) => {
                e.stopPropagation();
                removeSelectedItem(property.id);
            });

            const option = new Option(property.title, property.id, true, true);
            hiddenSelect.add(option);

            const resultItem = resultsContainer.querySelector(`.property-item[data-id="${property.id}"]`);
            if (resultItem) {
                resultItem.remove();
            }
        };

        const removeSelectedItem = (id) => {
            const item = selectedContainer.querySelector(`.property-item[data-id="${id}"]`);
            if (item) {
                item.remove();
            }

            const option = hiddenSelect.querySelector(`option[value="${id}"]`);
            if (option) {
                option.remove();
            }

            if (selectedContainer.children.length === 0 && selectedPlaceholder) {
                 selectedPlaceholder.style.display = 'block';
            }
        };

        searchInput.addEventListener('keyup', debounce((e) => {
            const searchTerm = e.target.value.trim();
            if (searchTerm.length > 2) {
                search(searchTerm);
            } else if (searchTerm.length === 0) {
                resultsContainer.innerHTML = '<div class="property-item-placeholder">Type to search for properties.</div>';
            }
        }, 300));

        selectedContainer.querySelectorAll('.remove-property').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const propertyId = e.target.closest('.property-item').dataset.id;
                removeSelectedItem(propertyId);
            });
        });
    });
});
