document.addEventListener('DOMContentLoaded', function() {
    // Logic for the Supplier Communication tab
    const sendWhatsAppBtn = document.getElementById('send-whatsapp-supplier-btn');
    const whatsAppSentCheckbox = document.getElementById('jform_whatsapp_sent');
    const loadTemplateBtn = document.getElementById('load-supplier-template-btn');

    if (sendWhatsAppBtn && whatsAppSentCheckbox) {
        sendWhatsAppBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const phone = options.supplier_phone;

            if (phone) {
                const supplierMessageEditor = Joomla.editors.instances['jform_supplier_message'];
                const message = supplierMessageEditor.getValue();
                const url = 'https://wa.me/' + phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(message);
                window.open(url, '_blank');
                whatsAppSentCheckbox.checked = true;
            } else {
                alert('Supplier phone number is not available.');
            }
        });
    }

    if (loadTemplateBtn) {
        loadTemplateBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const url = options.getSupplierTemplate;
            const requestId = document.getElementById('item-form').id.value;

            Joomla.request({
                url: url,
                method: 'POST',
                data: JSON.stringify({ id: requestId, [Joomla.getOptions('csrf.token')]: 1 }),
                headers: {
                    'Content-Type': 'application/json'
                },
                onSuccess: function(response) {
                    const data = JSON.parse(response);
                    if (data.success && data.data) {
                        const supplierMessageEditor = Joomla.editors.instances['jform_supplier_message'];
                        supplierMessageEditor.setValue(data.data);
                    } else {
                        Joomla.renderMessages({'error': [data.message]});
                    }
                },
                onError: function(xhr) {
                     Joomla.renderMessages({'error': ['An error occurred: ' + xhr.statusText]});
                }
            });
        });
    }

    const sendEmailBtn = document.getElementById('send-supplier-email-btn');
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const url = options.sendSupplierMessage;
            const supplierMessageEditor = Joomla.editors.instances['jform_supplier_message'];
            const message = supplierMessageEditor.getValue();
            const whatsappSent = document.getElementById('jform_whatsapp_sent').checked;
            const requestId = document.getElementById('item-form').id.value;

            if (!message && !whatsappSent) {
                alert('Please enter a message or check the "WhatsApp Sent" box.');
                return;
            }

            Joomla.request({
                url: url,
                method: 'POST',
                data: JSON.stringify({
                    id: requestId,
                    supplier_message: message,
                    whatsapp_sent: whatsappSent,
                    [Joomla.getOptions('csrf.token')]: 1
                }),
                headers: {
                    'Content-Type': 'application/json'
                },
                onSuccess: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        Joomla.renderMessages({'message': [data.message]});
                        // Optionally, clear the form and reload the history
                        supplierMessageEditor.setValue('');
                        document.getElementById('jform_whatsapp_sent').checked = false;
                        // A full reload is easiest to refresh the history
                        location.reload();
                    } else {
                        Joomla.renderMessages({'error': [data.message]});
                    }
                },
                onError: function(xhr) {
                     Joomla.renderMessages({'error': ['An error occurred: ' + xhr.statusText]});
                }
            });
        });
    }
});
