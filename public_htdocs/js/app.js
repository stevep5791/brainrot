// Privacy-first Brain Rot ML Training Data Collection
// No tracking, no cookies, no personal data collection

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');
            
            // Clear form message when switching tabs
            hideMessage();
        });
    });

    // File upload functionality
    const fileDropArea = document.getElementById('file-drop-area');
    const fileInput = document.getElementById('image-input');
    const selectFileBtn = document.querySelector('.select-file-btn');
    const filePreview = document.getElementById('file-preview');
    const previewImage = document.getElementById('preview-image');
    const removeFileBtn = document.querySelector('.remove-file-btn');

    // File selection button click
    selectFileBtn.addEventListener('click', function() {
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    // Drag and drop functionality
    fileDropArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });

    fileDropArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
    });

    fileDropArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            
            // Check if it's an image
            if (file.type.startsWith('image/')) {
                fileInput.files = files; // Set the file input
                handleFileSelect(file);
            } else {
                showMessage('Please drop an image file only.', 'error');
            }
        }
    });

    // Remove file functionality
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.style.display = 'none';
        fileDropArea.querySelector('.drop-zone').style.display = 'flex';
    });

    // Handle file selection and preview
    function handleFileSelect(file) {
        // Validate file type
        const allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/mov', 'video/quicktime'];
        const isImage = allowedImageTypes.includes(file.type);
        const isVideo = allowedVideoTypes.includes(file.type);
        
        if (!isImage && !isVideo) {
            showMessage('Please select a valid image (JPEG, PNG, GIF, WebP) or video (MP4, WebM, MOV) file.', 'error');
            return;
        }

        // Validate file size (50MB for videos, 10MB for images)
        const maxSize = isVideo ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
        if (file.size > maxSize) {
            const maxSizeText = isVideo ? '50MB' : '10MB';
            showMessage(`File is too large. Maximum size is ${maxSizeText}.`, 'error');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('preview-image');
            const previewVideo = document.getElementById('preview-video');
            const fileInfo = document.getElementById('file-info');
            
            if (isImage) {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
                previewVideo.style.display = 'none';
                fileInfo.textContent = `Image: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            } else if (isVideo) {
                previewVideo.src = e.target.result;
                previewVideo.style.display = 'block';
                previewImage.style.display = 'none';
                fileInfo.textContent = `Video: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            }
            
            fileDropArea.querySelector('.drop-zone').style.display = 'none';
            filePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    // Form submission
    const form = document.getElementById('brainrot-form');
    const submitBtn = document.getElementById('submit-btn');
    const clearBtn = document.getElementById('clear-btn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        // Create FormData
        const formData = new FormData(form);

        // Submit via fetch
        fetch('submit.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                clearForm();
            } else {
                showMessage(data.error || 'Submission failed. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            showMessage('Network error. Please check your connection and try again.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit for ML Training';
        });
    });

    // Clear form functionality
    clearBtn.addEventListener('click', function() {
        clearForm();
        hideMessage();
    });

    // Form validation
    function validateForm() {
        const textContent = document.getElementById('text-content').value.trim();
        const imageFile = fileInput.files.length > 0;
        const selectedCategories = document.querySelectorAll('input[name="categories[]"]:checked');

        // Check if content is provided
        if (!textContent && !imageFile) {
            showMessage('Please provide either text content or an image.', 'error');
            return false;
        }

        // Check if categories are selected
        if (selectedCategories.length === 0) {
            showMessage('Please select at least one brain rot category.', 'error');
            return false;
        }

        // Validate text length if provided
        if (textContent && textContent.length > 10000) {
            showMessage('Text content is too long. Maximum 10,000 characters allowed.', 'error');
            return false;
        }

        return true;
    }

    // Clear form function
    function clearForm() {
        // Clear text content
        document.getElementById('text-content').value = '';
        
        // Clear image
        fileInput.value = '';
        filePreview.style.display = 'none';
        fileDropArea.querySelector('.drop-zone').style.display = 'flex';
        
        // Uncheck all categories
        document.querySelectorAll('input[name="categories[]"]').forEach(cb => {
            cb.checked = false;
        });
        
        // Reset to text tab
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        document.querySelector('.tab-btn[data-tab="text"]').classList.add('active');
        document.getElementById('text-tab').classList.add('active');
    }

    // Message display functions
    function showMessage(message, type) {
        const messageDiv = document.getElementById('form-message');
        messageDiv.textContent = message;
        messageDiv.className = `form-message ${type}`;
        messageDiv.style.display = 'block';
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(hideMessage, 5000);
        }
    }

    function hideMessage() {
        const messageDiv = document.getElementById('form-message');
        messageDiv.style.display = 'none';
        messageDiv.className = 'form-message';
    }

    // Paste event handler for text area (including image paste)
    document.getElementById('text-content').addEventListener('paste', function(e) {
        // Check if clipboard contains files (images)
        const items = e.clipboardData?.items;
        if (items) {
            for (let item of items) {
                if (item.type.startsWith('image/')) {
                    e.preventDefault();
                    const file = item.getAsFile();
                    if (file) {
                        // Switch to image tab and handle the pasted image
                        document.querySelector('.tab-btn[data-tab="image"]').click();
                        fileInput.files = new FileList();
                        
                        // Create a new FileList with the pasted image (mobile compatible)
                        try {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            fileInput.files = dataTransfer.files;
                        } catch (e) {
                            // Fallback for older mobile browsers
                            console.log('DataTransfer not supported, using direct file handling');
                        }
                        
                        handleFileSelect(file);
                        showMessage('Image pasted successfully! Please select categories below.', 'success');
                        return;
                    }
                }
            }
        }
        
        // Small delay to allow text paste to complete
        setTimeout(() => {
            hideMessage();
        }, 100);
    });

    // Character counter for text area
    const textArea = document.getElementById('text-content');
    const maxLength = 10000;
    
    // Create character counter element
    const counterDiv = document.createElement('div');
    counterDiv.className = 'character-counter';
    counterDiv.style.cssText = 'text-align: right; margin-top: 5px; font-size: 0.9rem; color: #7f8c8d;';
    textArea.parentNode.appendChild(counterDiv);
    
    function updateCharacterCount() {
        const currentLength = textArea.value.length;
        counterDiv.textContent = `${currentLength}/${maxLength} characters`;
        
        if (currentLength > maxLength * 0.9) {
            counterDiv.style.color = '#e74c3c';
        } else if (currentLength > maxLength * 0.8) {
            counterDiv.style.color = '#f39c12';
        } else {
            counterDiv.style.color = '#7f8c8d';
        }
    }
    
    textArea.addEventListener('input', updateCharacterCount);
    textArea.addEventListener('paste', () => setTimeout(updateCharacterCount, 10));
    
    // Initial count
    updateCharacterCount();

    // Prevent right-click context menu on sensitive areas (privacy enhancement)
    document.addEventListener('contextmenu', function(e) {
        // Allow context menu on form inputs for accessibility
        if (!e.target.matches('input, textarea')) {
            e.preventDefault();
        }
    });

    // Log that the application loaded successfully (no personal data)
    console.log('Brain Rot ML Training Data Collection Interface loaded - Privacy Mode Active');
});