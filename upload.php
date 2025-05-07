<?php
// Inisialisasi session jika diperlukan
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CV - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="upload-container text-center">
            <h1 class="mt-5 mb-4">Upload CV Anda</h1>
            <div class="card p-4 shadow upload-form">
                <div class="card-body">
                    <p class="card-text mb-4">Unggah CV Anda dalam format PDF, DOC, atau DOCX. Kami akan menganalisis CV Anda untuk menemukan lowongan kerja yang paling sesuai.</p>
                    
                    <form action="process_cv.php" method="post" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="cv_file" class="form-label">Pilih File CV</label>
                            <input class="form-control" type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" required>
                            <div class="form-text">Format yang didukung: PDF, DOC, DOCX (Maks. 5MB)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bahasa CV</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="cv_language" id="lang_id" value="id" checked>
                                <label class="form-check-label" for="lang_id">Bahasa Indonesia</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="cv_language" id="lang_en" value="en">
                                <label class="form-check-label" for="lang_en">Bahasa Inggris</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Cari Lowongan</button>
                        <a href="index.html" class="btn btn-outline-secondary ms-2">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
