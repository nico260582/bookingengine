document.addEventListener('DOMContentLoaded', function() {
    const attachmentInput = document.getElementById('attachments');
    const attachmentList = document.getElementById('attachment-list');
    const form = document.querySelector('form[action*="communication.addClientMessage"]');
    let uploadedFiles = [];

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function(e) {
            for (const file of e.target.files) {
                uploadFile(file);
            }
        });
    }

    function uploadFile(file) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        const requestId = document.querySelector('input[name="request_id"]') ? document.querySelector('input[name="request_id"]').value : new URLSearchParams(window.location.search).get('request_id');

        formData.append('attachment', file);
        formData.append('option', 'com_bookingmanager');
        formData.append('task', 'communication.uploadAttachment');
        formData.append('request_id', requestId);
        formData.append(Joomla.getOptions('csrf.token'), 1);

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

        xhr.open('POST', 'index.php', true);
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
