document.addEventListener('DOMContentLoaded', function() {
    const attachmentInput = document.getElementById('attachments');
    const attachmentList = document.getElementById('attachment-list');
    const form = document.getElementById('item-form');
    let uploadedFiles = [];

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length === 0) {
                return;
            }

            const options = Joomla.getOptions('com_bookingmanager.admin');
            const url = options.upload;
            const token = Joomla.getOptions('csrf.token');
            const id = document.querySelector('input[name="id"]').value;

            for (let i = 0; i < files.length; i++) {
                const formData = new FormData();
                formData.append('attachment', files[i]);
                formData.append('id', id);
                formData.append(token, '1');

                fetch(url, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        uploadedFiles.push(data.data.filePath);
                        const fileItem = document.createElement('div');
                        fileItem.textContent = files[i].name + ' (Uploaded)';
                        attachmentList.appendChild(fileItem);
                    } else {
                        Joomla.renderMessages({'error': [data.message]});
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Joomla.renderMessages({'error': ['An error occurred during upload.']});
                });
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            // Add the list of uploaded files to a hidden input before submitting
            if (uploadedFiles.length > 0) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'uploaded_attachments';
                hiddenInput.value = JSON.stringify(uploadedFiles);
                form.appendChild(hiddenInput);
            }
        });
    }
});
