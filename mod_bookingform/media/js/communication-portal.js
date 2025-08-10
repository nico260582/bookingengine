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
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    fileElement.querySelector('.status').textContent = '✔';
                    uploadedFiles.push(response.data.filePath);
                } else {
                    fileElement.querySelector('.status').textContent = '✖';
                    alert('Upload failed: ' + response.message);
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
