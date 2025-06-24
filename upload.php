<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CV - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/upload.css">
</head>
<body>
    <div class="container">
        <div class="upload-container">
            <!-- Header -->
            <div class="text-center mb-4">
                <a href="index.html" class="navbar-brand">
                    <i class="fas fa-briefcase"></i>
                    JobMatch
                </a>
                <h1>Upload CV Anda</h1>
            </div>
            
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step active">
                    <div class="step-icon">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <div class="step-label">Upload CV</div>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="step-label">Proses</div>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="step-label">Hasil</div>
                </div>
            </div>
            
            <!-- Upload Form -->
            <div class="card upload-form">
                <div class="card-body">
                    <div class="upload-icon-wrapper">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    
                    <p class="card-text">
                        Unggah CV Anda dalam format PDF, DOC, atau DOCX. Sistem AI kami akan menganalisis CV Anda menggunakan metode Naive Bayes untuk menemukan lowongan kerja yang paling sesuai.
                    </p>
                    
                    <form action="process_cv.php" method="post" enctype="multipart/form-data" id="uploadForm">
                        <!-- Upload Area -->
                        <div class="upload-area" id="dropZone">
                            <input class="form-control" type="file" id="cv_file" name="cv_file" 
                                accept=".pdf,.doc,.docx" required onchange="validateFile(this)">
                            <div class="upload-content">
                                <div class="upload-icon-container">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <div class="upload-icon-circle"></div>
                                </div>
                                <div class="upload-text">
                                    <label for="cv_file" class="upload-label">
                                        Klik atau seret file CV Anda ke sini
                                    </label>
                                    <div class="form-text">
                                        Format yang didukung: PDF, DOC, DOCX (Maks. 5MB)
                                    </div>
                                </div>
                            </div>
                            <div id="fileError" class="invalid-feedback"></div>
                        </div>

                        <!-- Selected File Preview -->
                        <div class="selected-file d-none" id="selectedFile">
                            <div class="selected-file-info">
                                <i class="fas fa-file-pdf file-icon"></i>
                                <span class="file-name">Nama File</span>
                                <button type="button" class="btn-remove" onclick="removeFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <span class="spinner-border spinner-border-sm d-none" id="loadingSpinner" role="status"></span>
                                <span id="submitText">
                                    <i class="fas fa-search me-2"></i>
                                    Analisis CV
                                </span>
                            </button>
                            <a href="index.html" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    // Drag and drop functionality
    const dropZone = document.getElementById('dropZone');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('drag-over');
    }

    function unhighlight(e) {
        dropZone.classList.remove('drag-over');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        const fileInput = document.getElementById('cv_file');
        
        fileInput.files = files;
        validateFile(fileInput);
    }

    function validateFile(input) {
        const file = input.files[0];
        const fileError = document.getElementById('fileError');
        const submitBtn = document.getElementById('submitBtn');
        const selectedFile = document.getElementById('selectedFile');
        const fileName = selectedFile.querySelector('.file-name');
        
        if (file) {
            const allowedTypes = [
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!allowedTypes.includes(file.type)) {
                input.value = '';
                fileError.textContent = 'Format file tidak didukung. Silakan upload file PDF, DOC, atau DOCX.';
                input.classList.add('is-invalid');
                submitBtn.disabled = true;
                selectedFile.classList.add('d-none');
                return;
            }
            
            if (file.size > maxSize) {
                input.value = '';
                fileError.textContent = 'Ukuran file terlalu besar. Maksimal 5MB.';
                input.classList.add('is-invalid');
                submitBtn.disabled = true;
                selectedFile.classList.add('d-none');
                return;
            }
            
            input.classList.remove('is-invalid');
            submitBtn.disabled = false;
            selectedFile.classList.remove('d-none');
            fileName.textContent = file.name;
        }
    }

    function removeFile() {
        const input = document.getElementById('cv_file');
        const submitBtn = document.getElementById('submitBtn');
        const selectedFile = document.getElementById('selectedFile');
        
        input.value = '';
        submitBtn.disabled = true;
        selectedFile.classList.add('d-none');
    }

    document.getElementById('uploadForm').addEventListener('submit', function() {
        document.getElementById('loadingSpinner').classList.remove('d-none');
        document.getElementById('submitText').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        document.getElementById('submitBtn').disabled = true;
    });
    </script>
</body>
</html>
