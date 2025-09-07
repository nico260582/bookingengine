document.addEventListener('DOMContentLoaded', function() {
    // WhatsApp button logic
    const sendWhatsAppBtn = document.getElementById('send-whatsapp-supplier-btn');
    if (sendWhatsAppBtn) {
        sendWhatsAppBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const phone = options.supplier_phone;

            if (phone) {
                const supplierMessageEditor = Joomla.editors.instances['jform_supplier_message'];
                const message = supplierMessageEditor.getValue();

                // Convert HTML to plain text for the WhatsApp link
                function htmlToPlainText(html) {
                    let temp = document.createElement("div");
                    let text = html.replace(/<p>/gi, "").replace(/<\/p>|<br\s*\/?>/gi, "\n");
                    temp.innerHTML = text;
                    return temp.textContent || temp.innerText || "";
                }
                const plainTextMessage = htmlToPlainText(message);
                const url = 'https://wa.me/' + phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(plainTextMessage);
                window.open(url, '_blank');

                // Now, also save this action to the database
                const saveUrl = options.logWhatsAppMessage; // Use the new dedicated task
                const requestId = document.querySelector('#item-form input[name="id"]').value;
                const token = Joomla.getOptions('csrf.token');

                const formData = new FormData();
                formData.append('id', requestId);
                formData.append('supplier_message', message); // Log the original HTML message
                formData.append('whatsapp_sent', '1');
                formData.append(token, '1');

                fetch(saveUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    if (text.includes('"success":true')) {
                        Joomla.renderMessages({'message': ['WhatsApp communication has been logged successfully.']});
                        window.onbeforeunload = null;
                        window.location.reload();
                    } else {
                        console.error("Server returned an unexpected response for WhatsApp log:", text);
                        Joomla.renderMessages({'error': ['An unknown error occurred. Please check the browser console (F12) for details.']});
                    }
                })
                .catch(error => {
                    console.error('Error during WhatsApp log fetch:', error);
                    Joomla.renderMessages({'error': ['An error occurred.']});
                });
            } else {
                alert('Supplier phone number is not available.');
            }
        });
    }

    // Load Template button logic
    const loadTemplateBtn = document.getElementById('load-supplier-template-btn');
    if (loadTemplateBtn) {
        loadTemplateBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const url = options.getSupplierTemplate;
            const requestId = document.querySelector('#item-form input[name="id"]').value;
            const token = Joomla.getOptions('csrf.token');

            const formData = new FormData();
            formData.append('id', requestId);
            formData.append(token, '1');

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const supplierMessageEditor = Joomla.editors.instances['jform_supplier_message'];
                    supplierMessageEditor.setValue(data.data);
                } else {
                    Joomla.renderMessages({'error': [data.message || 'An unknown error occurred while loading the template.']});
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Joomla.renderMessages({'error': ['An error occurred.']});
            });
        });
    }

    // Send Email button logic
    const sendEmailBtn = document.getElementById('send-supplier-email-btn');
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const url = options.sendSupplierMessage;
            const requestId = document.querySelector('#item-form input[name="id"]').value;
            const token = Joomla.getOptions('csrf.token');
            const supplierMessageEditor = Joomla.editors.instances['jform_supplier_message'];
            const message = supplierMessageEditor.getValue();
            const whatsappSent = document.getElementById('jform_whatsapp_sent').checked;

            if (!message && !whatsappSent) {
                alert('Please enter a message or check the "WhatsApp Sent" box.');
                return;
            }

            const attachmentInput = document.getElementById('supplier_attachment');
            const attachmentFile = attachmentInput.files[0];

            const formData = new FormData();
            formData.append('id', requestId);
            formData.append('supplier_message', message);
            formData.append('whatsapp_sent', whatsappSent ? '1' : '0');
            formData.append(token, '1');
            if (attachmentFile) {
                formData.append('supplier_attachment', attachmentFile);
            }

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                if (text.includes('"success":true')) {
                    // Try to parse the JSON to get the message, but don't fail if it's broken
                    let message = 'Message sent successfully.';
                    try {
                        const data = JSON.parse(text);
                        message = data.message || message;
                    } catch (e) {
                        // Ignore parse error, use default message
                    }
                    Joomla.renderMessages({'message': [message]});
                    supplierMessageEditor.setValue('');
                    document.getElementById('jform_whatsapp_sent').checked = false;
                    window.onbeforeunload = null;
                    window.location.reload();
                } else {
                    console.error("Server returned an unexpected response for Send Email:", text);
                    Joomla.renderMessages({'error': ['An unknown error occurred. Please check the browser console (F12) for details.']});
                }
            })
            .catch(error => {
                console.error('Error during Send Email fetch:', error);
                Joomla.renderMessages({'error': ['An error occurred.']});
            });
        });
    }
});
