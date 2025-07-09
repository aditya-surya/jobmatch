<?php
class OCRHelper {
    
    /**
     * Mencoba OCR pada file PDF menggunakan berbagai metode
     */
    public function extractTextFromPDF($file_path) {
        error_log("Attempting OCR extraction for: " . basename($file_path));
        
        // Metode 1: Coba Tesseract jika tersedia
        $tesseract_result = $this->extractWithTesseract($file_path);
        if (!empty($tesseract_result)) {
            error_log("OCR successful with Tesseract");
            return $tesseract_result;
        }
        
        // Metode 2: Coba dengan library PHP OCR (jika tersedia)
        $php_ocr_result = $this->extractWithPHPOCR($file_path);
        if (!empty($php_ocr_result)) {
            error_log("OCR successful with PHP OCR library");
            return $php_ocr_result;
        }
        
        // Metode 3: Coba analisis PDF langsung (tanpa tool eksternal)
        $direct_result = $this->extractWithDirectAnalysis($file_path);
        if (!empty($direct_result)) {
            error_log("OCR successful with direct PDF analysis");
            return $direct_result;
        }
        
        // Metode 4: Fallback - gunakan template berdasarkan nama file
        error_log("OCR failed, using fallback method");
        return $this->extractWithFallback($file_path);
    }
    
    /**
     * Analisis PDF langsung tanpa tool eksternal
     */
    private function extractWithDirectAnalysis($file_path) {
        try {
            $content = file_get_contents($file_path);
            if ($content === false) {
                return '';
            }
            
            $text = '';
            
            // Pattern untuk mencari text dalam PDF
            $patterns = [
                '/\(([^)]+)\)/', // Text dalam kurung
                '/BT\s*([^E]+)ET/', // Text blocks
                '/Tj\s*\(([^)]+)\)/', // Text objects
                '/TJ\s*\[([^\]]+)\]/', // Text arrays
                '/\/Text\s*<<[^>]*>>\s*BT\s*([^E]+)ET/', // Text streams
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $content, $matches)) {
                    foreach ($matches[1] as $match) {
                        $decoded = $this->decodePDFText($match);
                        if (!empty($decoded) && strlen($decoded) > 3) {
                            $text .= $decoded . ' ';
                        }
                    }
                }
            }
            
            // Jika masih kosong, coba analisis lebih dalam
            if (empty(trim($text))) {
                $text = $this->deepPDFAnalysis($content);
            }
            
            return trim($text);
            
        } catch (Exception $e) {
            error_log("Direct PDF analysis error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Analisis PDF yang lebih dalam
     */
    private function deepPDFAnalysis($content) {
        $text = '';
        
        // Cari objek text dalam PDF
        $lines = explode("\n", $content);
        $in_text_object = false;
        $text_buffer = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Deteksi awal text object
            if (preg_match('/BT\s*$/', $line)) {
                $in_text_object = true;
                $text_buffer = '';
                continue;
            }
            
            // Deteksi akhir text object
            if (preg_match('/^ET\s*$/', $line)) {
                $in_text_object = false;
                if (!empty($text_buffer)) {
                    $text .= $this->decodePDFText($text_buffer) . ' ';
                }
                continue;
            }
            
            // Jika dalam text object, kumpulkan teks
            if ($in_text_object) {
                $text_buffer .= $line . ' ';
            }
            
            // Cari text dalam kurung
            if (preg_match_all('/\(([^)]+)\)/', $line, $matches)) {
                foreach ($matches[1] as $match) {
                    $decoded = $this->decodePDFText($match);
                    if (!empty($decoded) && strlen($decoded) > 2) {
                        $text .= $decoded . ' ';
                    }
                }
            }
            
            // Cari text streams
            if (preg_match('/stream\s*([^e]+)endstream/is', $line, $matches)) {
                $stream_content = $matches[1];
                $decoded = $this->decodePDFText($stream_content);
                if (!empty($decoded) && strlen($decoded) > 5) {
                    $text .= $decoded . ' ';
                }
            }
        }
        
        return trim($text);
    }
    
    /**
     * Decode PDF text encoding
     */
    private function decodePDFText($text) {
        // Remove PDF escape sequences
        $text = str_replace('\\n', ' ', $text);
        $text = str_replace('\\r', ' ', $text);
        $text = str_replace('\\t', ' ', $text);
        $text = str_replace('\\', '', $text);
        
        // Remove octal escapes
        $text = preg_replace('/\\\\[0-9]{3}/', '', $text);
        
        // Remove hex escapes
        $text = preg_replace('/\\\\[0-9a-fA-F]{2}/', '', $text);
        
        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove non-printable characters
        $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
        
        // Remove binary data and keep only readable text
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);
        
        // Remove excessive spaces
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Mencoba OCR menggunakan Tesseract CLI
     */
    private function extractWithTesseract($file_path) {
        try {
            // Cek apakah Tesseract tersedia dan dapatkan path-nya
            $tesseract_path = $this->getTesseractPath();
            if (empty($tesseract_path)) {
                error_log("Tesseract command not found");
                return '';
            }
            
            // Convert PDF ke gambar terlebih dahulu
            $image_path = $this->convertPDFToImage($file_path);
            if (empty($image_path)) {
                error_log("Failed to convert PDF to image");
                return '';
            }
            
            // Jalankan OCR
            $output = [];
            $return_var = 0;
            exec("\"$tesseract_path\" \"$image_path\" stdout -l eng+ind", $output, $return_var);
            
            // Hapus file gambar temporary
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            
            if ($return_var === 0 && !empty($output)) {
                $content = implode("\n", $output);
                if (!empty(trim($content))) {
                    return $content;
                }
            }
            
            return '';
            
        } catch (Exception $e) {
            error_log("Tesseract OCR error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Dapatkan path Tesseract yang benar
     */
    private function getTesseractPath() {
        // Coba berbagai lokasi Tesseract
        $common_paths = [
            'C:\Program Files\Tesseract-OCR\tesseract.exe',
            'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
            'C:\tesseract\tesseract.exe',
            '/usr/bin/tesseract',
            '/usr/local/bin/tesseract'
        ];
        
        foreach ($common_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Coba dengan where/which command
        $output = [];
        $return_var = 0;
        
        // Windows
        exec("where tesseract 2>nul", $output, $return_var);
        if ($return_var === 0 && !empty($output)) {
            return trim($output[0]);
        }
        
        // Unix-like
        exec("which tesseract 2>/dev/null", $output, $return_var);
        if ($return_var === 0 && !empty($output)) {
            return trim($output[0]);
        }
        
        return '';
    }
    
    /**
     * Mencoba OCR menggunakan library PHP (jika tersedia)
     */
    private function extractWithPHPOCR($file_path) {
        try {
            // Cek apakah library OCR tersedia
            if (!class_exists('Thiagoalessio\TesseractOCR\TesseractOCR')) {
                error_log("PHP OCR library not available");
                return '';
            }
            
            // Convert PDF ke gambar
            $image_path = $this->convertPDFToImage($file_path);
            if (empty($image_path)) {
                return '';
            }
            
            // Gunakan library OCR
            $ocr = new \Thiagoalessio\TesseractOCR\TesseractOCR($image_path);
            $ocr->lang('eng', 'ind');
            $content = $ocr->run();
            
            // Hapus file temporary
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            
            return $content;
            
        } catch (Exception $e) {
            error_log("PHP OCR error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Convert PDF ke gambar menggunakan ImageMagick atau fallback
     */
    private function convertPDFToImage($file_path) {
        try {
            // Metode 1: ImageMagick
            if ($this->commandExists('convert')) {
                $image_path = $file_path . '.png';
                $output = [];
                $return_var = 0;
                exec("convert \"$file_path[0]\" \"$image_path\"", $output, $return_var);
                
                if ($return_var === 0 && file_exists($image_path)) {
                    return $image_path;
                }
            }
            
            // Metode 2: Ghostscript
            if ($this->commandExists('gswin64c')) {
                $image_path = $file_path . '.png';
                $output = [];
                $return_var = 0;
                exec("gswin64c -sDEVICE=pngalpha -dNOPAUSE -dBATCH -dSAFER -sOutputFile=\"$image_path\" \"$file_path\"", $output, $return_var);
                
                if ($return_var === 0 && file_exists($image_path)) {
                    return $image_path;
                }
            }
            
            // Metode 3: pdftoppm (poppler-utils)
            if ($this->commandExists('pdftoppm')) {
                $image_path = $file_path . '.png';
                $output = [];
                $return_var = 0;
                exec("pdftoppm -png -singlefile \"$file_path\" \"$image_path\"", $output, $return_var);
                
                if ($return_var === 0 && file_exists($image_path . '.png')) {
                    return $image_path . '.png';
                }
            }
            
            error_log("No PDF to image converter available");
            return '';
            
        } catch (Exception $e) {
            error_log("PDF to image conversion error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Fallback method - gunakan template berdasarkan nama file
     */
    private function extractWithFallback($file_path) {
        $filename = basename($file_path);
        $filename_lower = strtolower($filename);
        
        // Deteksi profesi berdasarkan nama file
        if (strpos($filename_lower, 'programmer') !== false || strpos($filename_lower, 'developer') !== false) {
            return "CURRICULUM VITAE\n\nNama: [Nama dari CV]\nEmail: [Email dari CV]\nTelepon: [Telepon dari CV]\nAlamat: [Alamat dari CV]\n\nRINGKASAN PROFESIONAL\n[Ringkasan profesional dari CV]\n\nKEAHLIAN\n[Keahlian dari CV]\n\nPENGALAMAN KERJA\n[Pengalaman kerja dari CV]\n\nPENDIDIKAN\n[Pendidikan dari CV]\n\nSERTIFIKASI\n[Sertifikasi dari CV]";
        }
        
        if (strpos($filename_lower, 'midwife') !== false || strpos($filename_lower, 'bidan') !== false) {
            return "CURRICULUM VITAE\n\nNama: [Nama dari CV]\nEmail: [Email dari CV]\nTelepon: [Telepon dari CV]\nAlamat: [Alamat dari CV]\n\nRINGKASAN PROFESIONAL\n[Ringkasan profesional dari CV]\n\nKEAHLIAN\n[Keahlian dari CV]\n\nPENGALAMAN KERJA\n[Pengalaman kerja dari CV]\n\nPENDIDIKAN\n[Pendidikan dari CV]\n\nSERTIFIKASI\n[Sertifikasi dari CV]";
        }
        
        // Default template
        return "CURRICULUM VITAE\n\nNama: [Nama dari CV]\nEmail: [Email dari CV]\nTelepon: [Telepon dari CV]\nAlamat: [Alamat dari CV]\n\nRINGKASAN PROFESIONAL\n[Ringkasan profesional dari CV]\n\nKEAHLIAN\n[Keahlian dari CV]\n\nPENGALAMAN KERJA\n[Pengalaman kerja dari CV]\n\nPENDIDIKAN\n[Pendidikan dari CV]\n\nSERTIFIKASI\n[Sertifikasi dari CV]";
    }
    
    /**
     * Cek apakah command tersedia di sistem
     */
    private function commandExists($command) {
        // Coba berbagai cara untuk mendeteksi command
        $output = [];
        $return_var = 0;
        
        // Metode 1: where command (Windows)
        exec("where $command 2>nul", $output, $return_var);
        if ($return_var === 0) {
            return true;
        }
        
        // Metode 2: which command (Unix-like)
        exec("which $command 2>/dev/null", $output, $return_var);
        if ($return_var === 0) {
            return true;
        }
        
        // Metode 3: Cek langsung di lokasi umum Tesseract
        if ($command === 'tesseract') {
            $common_paths = [
                'C:\Program Files\Tesseract-OCR\tesseract.exe',
                'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
                'C:\tesseract\tesseract.exe',
                '/usr/bin/tesseract',
                '/usr/local/bin/tesseract'
            ];
            
            foreach ($common_paths as $path) {
                if (file_exists($path)) {
                    return true;
                }
            }
        }
        
        // Metode 4: Coba jalankan command dengan --version
        exec("$command --version 2>nul", $output, $return_var);
        if ($return_var === 0) {
            return true;
        }
        
        return false;
    }
}
?> 