<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CV - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/upload.css">
</head>
<body>
    <div class="container">
        <div class="upload-container text-center">
            <h1 class="mt-5 mb-4">Upload CV Anda</h1>
            
            <div class="card p-4 shadow upload-form">
                <div class="card-body">
                    <p class="card-text mb-4">
                        Unggah CV Anda dalam format PDF, DOC, DOCX, atau TXT. Kami akan menganalisis CV Anda untuk menemukan lowongan kerja yang paling sesuai.
                    </p>
                    <form action="process_cv.php" method="post" enctype="multipart/form-data" id="uploadForm">
                        <div class="mb-4">
                            <label for="cv_file" class="form-label">Pilih File CV</label>
                            <input class="form-control" type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx,.txt" required 
                                onchange="validateFile(this)">
                            <div class="form-text">Format yang didukung: PDF, DOC, DOCX, TXT (Maks. 5MB)</div>
                            <div id="fileError" class="invalid-feedback"></div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm d-none" id="loadingSpinner" role="status" aria-hidden="true"></span>
                                <span id="submitText">Cari Lowongan</span>
                            </button>
                            <a href="index.html" class="btn btn-outline-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function validateFile(input) {
        const file = input.files[0];
        const fileError = document.getElementById('fileError');
        const submitBtn = document.getElementById('submitBtn');
        
        if (file) {
            const allowedTypes = [
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!allowedTypes.includes(file.type)) {
                input.value = '';
                fileError.textContent = 'Format file tidak didukung. Silakan upload file PDF, DOC, DOCX, atau TXT.';
                input.classList.add('is-invalid');
                submitBtn.disabled = true;
                return;
            }
            
            if (file.size > maxSize) {
                input.value = '';
                fileError.textContent = 'Ukuran file terlalu besar. Maksimal 5MB.';
                input.classList.add('is-invalid');
                submitBtn.disabled = true;
                return;
            }
            
            input.classList.remove('is-invalid');
            submitBtn.disabled = false;
        }
    }

    document.getElementById('uploadForm').addEventListener('submit', function() {
        document.getElementById('loadingSpinner').classList.remove('d-none');
        document.getElementById('submitText').textContent = 'Memproses...';
        document.getElementById('submitBtn').disabled = true;
    });
    </script>
</body>
</html>
