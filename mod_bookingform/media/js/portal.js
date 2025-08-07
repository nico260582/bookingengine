document.addEventListener('DOMContentLoaded', function() {
    function convertDatesToLocalTime() {
        const dateElements = document.querySelectorAll('[data-utc-date]');

        dateElements.forEach(function(element) {
            const utcDate = element.getAttribute('data-utc-date');
            if (utcDate) {
                const date = new Date(utcDate);
                const options = {
                    year: 'numeric', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit', hour12: false
                };
                // Use browser's locale for formatting
                element.textContent = date.toLocaleString(undefined, options);
            }
        });
    }

    convertDatesToLocalTime();
});
