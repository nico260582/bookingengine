document.addEventListener('DOMContentLoaded', function() {
    const attachmentInput = document.getElementById('attachments');
    const attachmentList = document.getElementById('attachment-list');
    const form = document.querySelector('form[action*="addClientMessage"]');
    let uploadedFiles = [];

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function(e) {
            for (const file of e.target.files) {
                uploadFile(file);
            }
        });
    }

    function uploadFile(file) {
        const options = Joomla.getOptions('com_bookingmanager');
        if (!options || !options.booking_id) {
            alert('Error: Could not determine the booking ID. Please refresh the page and try again.');
            return;
        }

        const xhr = new XMLHttpRequest();
        const formData = new FormData();

        formData.append('attachment', file);
        formData.append('request_id', options.booking_id);
        formData.append(options.token, 1);

        const fileId = 'file-' + Date.now();
        const fileElement = document.createElement('div');
        fileElement.id = fileId;
        fileElement.innerHTML = `
            <span>${file.name}</span>
            <progress value="0" max="100"></progress>
            <span class="status"></span>
            <span class="delete-attachment" style="cursor: pointer; display: none;">&nbsp;&#10006;</span>
        `;
        attachmentList.appendChild(fileElement);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                fileElement.querySelector('progress').value = percentComplete;
            }
        });

        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        fileElement.querySelector('.status').textContent = '✔';
                        const deleteBtn = fileElement.querySelector('.delete-attachment');
                        deleteBtn.style.display = 'inline';
                        deleteBtn.dataset.filePath = response.data.filePath;
                        uploadedFiles.push(response.data.filePath);
                    } else {
                        fileElement.querySelector('.status').textContent = '✖';
                        alert('Upload failed: ' + (response.message || 'Unknown error'));
                    }
                } catch (e) {
                    fileElement.querySelector('.status').textContent = '✖';
                    alert('Upload failed: Invalid server response.');
                    console.error('Invalid JSON:', xhr.responseText);
                }
            } else {
                fileElement.querySelector('.status').textContent = '✖';
                alert('Upload failed with status: ' + xhr.status);
            }
        });

        xhr.addEventListener('error', function() {
            fileElement.querySelector('.status').textContent = '✖';
            alert('An error occurred during the upload.');
        });

        xhr.open('POST', options.urls.upload, true);
        xhr.send(formData);
    }

    attachmentList.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('delete-attachment')) {
            const deleteBtn = e.target;
            const filePath = deleteBtn.dataset.filePath;
            const fileElement = deleteBtn.closest('div');

            if (confirm('Are you sure you want to delete this attachment?')) {
                deleteAttachment(filePath, fileElement);
            }
        }
    });

    function deleteAttachment(filePath, fileElement) {
        const options = Joomla.getOptions('com_bookingmanager');
        const formData = new FormData();
        formData.append('filePath', filePath);
        formData.append('request_id', options.booking_id);
        formData.append(options.token, 1);

        fetch(options.urls.deleteAttachment, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove from UI
                fileElement.remove();
                // Remove from array of files to be submitted
                const index = uploadedFiles.indexOf(filePath);
                if (index > -1) {
                    uploadedFiles.splice(index, 1);
                }
            } else {
                alert('Failed to delete attachment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting attachment:', error);
            alert('An error occurred while deleting the attachment.');
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const uploadedFilesInput = document.createElement('input');
            uploadedFilesInput.type = 'hidden';
            uploadedFilesInput.name = 'uploaded_attachments';
            uploadedFilesInput.value = JSON.stringify(uploadedFiles);
            form.appendChild(uploadedFilesInput);
        });
    }
});
