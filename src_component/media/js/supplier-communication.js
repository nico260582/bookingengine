document.addEventListener('DOMContentLoaded', function() {
    // --- Elements ---
    const attachmentInput = document.getElementById('supplier_attachments');
    const attachmentList = document.getElementById('supplier_attachment_list');
    const sendEmailBtn = document.getElementById('send-supplier-email-btn');
    const sendWhatsAppBtn = document.getElementById('send-whatsapp-supplier-btn');
    const loadTemplateBtn = document.getElementById('load-supplier-template-btn');
    const conversationContainer = document.querySelector('#myTab .active .conversation-history-admin .timeline-admin');

    // --- State ---
    let uploadedSupplierFiles = []; // Holds paths of successfully uploaded files

    // --- Functions ---

    /**
     * Dynamically adds a new message to the top of the conversation history.
     * @param {object} messageData - The data for the new message.
     * @param {string} messageData.message - The HTML content of the message.
     * @param {string} messageData.author_name - The name of the author.
     * @param {string} messageData.sent_at - The timestamp.
     * @param {boolean} messageData.whatsapp_sent - If it was a WhatsApp message.
     */
    function addMessageToHistory(messageData) {
        if (!conversationContainer) return;

        const timelineItem = document.createElement('div');
        timelineItem.className = 'timeline-item-admin admin';

        const iconClass = messageData.whatsapp_sent ? 'icon-whatsapp' : 'icon-envelope';
        const iconTitle = messageData.whatsapp_sent ? 'Sent via WhatsApp' : 'Sent via Email';

        timelineItem.innerHTML = `
            <div class="timeline-content-admin">
                <div class="message-header-admin">
                    <span class="message-author-admin">${messageData.author_name || 'You'} (Admin)</span>
                    <div class="header-right-group">
                        <span class="message-date-admin">${messageData.sent_at}</span>
                        <span class="message-method-icon">
                            <i class="${iconClass}" title="${iconTitle}"></i>
                        </span>
                    </div>
                </div>
                <div class="message-body-admin">
                    ${messageData.message}
                </div>
            </div>
        `;

        // Remove the 'No messages yet' placeholder if it exists
        const placeholder = conversationContainer.querySelector('p');
        if (placeholder) {
            placeholder.remove();
        }

        conversationContainer.prepend(timelineItem);
    }

    /**
     * Resets the input form after a message is sent.
     */
    function resetForm() {
        if (Joomla.editors.instances['jform_supplier_message']) {
            Joomla.editors.instances['jform_supplier_message'].setValue('');
        }
        attachmentList.innerHTML = '';
        uploadedSupplierFiles = [];
        // Prevent the 'are you sure you want to leave' pop-up
        window.onbeforeunload = null;
    }

    /**
     * Handles the file deletion logic.
     * @param {string} filePath - Path of the file to delete.
     * @param {HTMLElement} elementToRemove - The UI element for the file.
     */
    function deleteAttachment(filePath, elementToRemove) {
        const options = Joomla.getOptions('com_bookingmanager.admin');
        const url = options.deleteSupplierAttachment; // Use a dedicated endpoint
        const token = Joomla.getOptions('csrf.token');

        const formData = new FormData();
        formData.append('filePath', filePath);
        formData.append(token, '1');

        fetch(url, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                elementToRemove.remove();
                uploadedSupplierFiles = uploadedSupplierFiles.filter(p => p !== filePath);
                Joomla.renderMessages({'message': ['Attachment deleted.']});
            } else {
                Joomla.renderMessages({'error': [data.message || 'Failed to delete attachment.']});
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Joomla.renderMessages({'error': ['An error occurred while deleting the attachment.']});
        });
    }

    // --- Event Listeners ---

    // Attachment uploader
    if (attachmentInput && attachmentList) {
        attachmentInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length === 0) return;

            const options = Joomla.getOptions('com_bookingmanager.admin');
            const url = options.upload;
            const token = Joomla.getOptions('csrf.token');
            const id = document.querySelector('input[name="id"]').value;
            attachmentInput.disabled = true;

            Array.from(files).forEach((file, index) => {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('id', id);
                formData.append(token, '1');

                const placeholder = document.createElement('div');
                placeholder.classList.add('attachment-item', 'uploading');
                placeholder.innerHTML = `<span>${file.name}</span> <span class="upload-status">Uploading...</span>`;
                attachmentList.appendChild(placeholder);

                fetch(url, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        placeholder.classList.remove('uploading');
                        placeholder.classList.add('success');
                        placeholder.innerHTML = `
                            <span class="icon-ok" style="color: green;"></span>
                            <span>${data.data.fileName}</span>
                            <button type="button" class="btn btn-mini btn-danger delete-attachment-btn">&times;</button>`;

                        const filePath = data.data.filePath;
                        uploadedSupplierFiles.push(filePath);

                        placeholder.querySelector('.delete-attachment-btn').addEventListener('click', () => deleteAttachment(filePath, placeholder));
                    } else {
                        placeholder.classList.remove('uploading');
                        placeholder.classList.add('failed');
                        placeholder.innerHTML = `<span>${file.name}</span> <span class="upload-status" style="color: red;">Failed: ${data.message}</span>`;
                        Joomla.renderMessages({'error': [`Upload failed for ${file.name}: ${data.message}`]});
                    }
                })
                .catch(error => {
                    placeholder.classList.remove('uploading');
                    placeholder.classList.add('failed');
                    placeholder.innerHTML = `<span>${file.name}</span> <span class="upload-status" style="color: red;">Error</span>`;
                    console.error('Error:', error);
                })
                .finally(() => {
                    if (index === files.length - 1) {
                        attachmentInput.disabled = false;
                        attachmentInput.value = '';
                    }
                });
            });
        });
    }

    // Send Email Button
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const url = options.sendSupplierMessage;
            const token = Joomla.getOptions('csrf.token');
            const id = document.querySelector('input[name="id"]').value;
            const message = Joomla.editors.instances['jform_supplier_message'].getValue();

            if (!message && uploadedSupplierFiles.length === 0) {
                alert('Please enter a message or add an attachment.');
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('supplier_message', message);
            formData.append('whatsapp_sent', '0');
            formData.append(token, '1');
            uploadedSupplierFiles.forEach(file => {
                formData.append('attachments[]', file);
            });

            sendEmailBtn.disabled = true;

            fetch(url, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Joomla.renderMessages({'message': [data.message]});
                    if (data.data) {
                        addMessageToHistory(data.data);
                    }
                    resetForm();
                } else {
                    Joomla.renderMessages({'error': [data.message]});
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => sendEmailBtn.disabled = false);
        });
    }

    // Log WhatsApp Button
    if (sendWhatsAppBtn) {
        sendWhatsAppBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const phone = options.supplier_phone;
            const message = Joomla.editors.instances['jform_supplier_message'].getValue();

            if (!phone) {
                alert('Supplier phone number is not available.');
                return;
            }

            const plainTextMessage = message.replace(/<[^>]*>?/gm, '');
            const waUrl = 'https://wa.me/' + phone.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(plainTextMessage);
            window.open(waUrl, '_blank');

            const logUrl = options.logWhatsAppMessage;
            const token = Joomla.getOptions('csrf.token');
            const id = document.querySelector('input[name="id"]').value;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('supplier_message', message);
            formData.append('whatsapp_sent', '1');
            formData.append(token, '1');

            sendWhatsAppBtn.disabled = true;

            fetch(logUrl, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Joomla.renderMessages({'message': [data.message]});
                    if (data.data) {
                        addMessageToHistory(data.data);
                    }
                    resetForm();
                } else {
                    Joomla.renderMessages({'error': [data.message]});
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => sendWhatsAppBtn.disabled = false);
        });
    }

    // Load Template Button
    if (loadTemplateBtn) {
        loadTemplateBtn.addEventListener('click', function() {
            const options = Joomla.getOptions('com_bookingmanager.admin');
            const url = options.getSupplierTemplate;
            const token = Joomla.getOptions('csrf.token');
            const id = document.querySelector('input[name="id"]').value;

            const formData = new FormData();
            formData.append('id', id);
            formData.append(token, '1');

            fetch(url, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Joomla.editors.instances['jform_supplier_message'].setValue(data.data);
                } else {
                    Joomla.renderMessages({'error': [data.message || 'An unknown error occurred while loading the template.']});
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
