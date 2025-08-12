function initPropertyAssignment(containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error('Property Assignment container not found:', containerId);
        return;
    }

    const supplierId = container.dataset.supplierId;
    const fieldId = container.dataset.fieldId;
    const fieldName = container.dataset.fieldName;
    const formToken = container.dataset.formToken;

    const searchInput = container.querySelector(`#${fieldId}_search`);
    const resultsContainer = container.querySelector(`#${fieldId}_results .property-list-results`);
    const selectedContainer = container.querySelector(`#${fieldId}_selected .property-list-selected`);
    const hiddenSelect = document.querySelector(`#${fieldId}_hidden_select`);
    const selectedPlaceholder = container.querySelector(`#${fieldId}_selected_placeholder`);

    if (!searchInput || !resultsContainer || !selectedContainer || !hiddenSelect) {
        console.error('One or more required elements are missing from the Property Assignment field.');
        return;
    }

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

        const url = `index.php?option=com_bookingmanager&task=properties.get&format=raw&supplier_id=${supplierId}&search=${encodeURIComponent(searchTerm)}&${formToken}=1`;

        try {
            const response = await fetch(url);

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Request failed with status ${response.status}: ${errorText}`);
            }

            const json = await response.json();

            if (json.success) {
                renderResults(json.data);
            } else {
                const errorMessage = json.message || 'An unknown error occurred on the server.';
                resultsContainer.innerHTML = `<div class="property-item-placeholder">${errorMessage}</div>`;
            }
        } catch (error) {
            console.error('Error fetching properties:', error);
            resultsContainer.innerHTML = `<div class="property-item-placeholder">Error: ${error.message}</div>`;
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
        } else {
            resultsContainer.innerHTML = '<div class="property-item-placeholder">Please type more than 2 characters.</div>';
        }
    }, 300));

    selectedContainer.querySelectorAll('.remove-property').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const propertyId = e.target.closest('.property-item').dataset.id;
            removeSelectedItem(propertyId);
        });
    });
}
