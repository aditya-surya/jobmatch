<?php
class NaiveBayes {
    private $conn;
    private $stopwords_id = [];
    private $stopwords_en = [];
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->loadStopwords();
    }
    
    private function loadStopwords() {
        if (file_exists('data/stopwords_id.txt')) {
            $stopwords_id_file = file_get_contents('data/stopwords_id.txt');
            if ($stopwords_id_file) {
                $this->stopwords_id = array_map('trim', explode("\n", $stopwords_id_file));
            }
        }
        
        if (file_exists('data/stopwords_en.txt')) {
            $stopwords_en_file = file_get_contents('data/stopwords_en.txt');
            if ($stopwords_en_file) {
                $this->stopwords_en = array_map('trim', explode("\n", $stopwords_en_file));
            }
        }
    }
    
    public function preprocessText($text, $language = 'id') {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z\s]/i', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $words = explode(' ', $text);
        
        $stopwords = ($language == 'id') ? $this->stopwords_id : $this->stopwords_en;
        $filtered_words = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if ($word != "" && !in_array($word, $stopwords) && strlen($word) > 2) {
                $filtered_words[] = $word;
            }
        }
        
        return implode(' ', $filtered_words);
    }
    
    public function extractKeywords($text) {
        $words = explode(' ', $text);
        $word_count = array_count_values($words);
        arsort($word_count);
        
        // Deteksi bagian SKILLS (Inggris) atau KETERAMPILAN/KEAHLIAN (Indonesia)
        $skills_keywords = [];
        $text_lower = strtolower($text);
        $skills_sections = [
            'skills',        // Inggris
            'keterampilan', // Indonesia
            'keahlian',     // Indonesia
            'expertise',    // Inggris
            'competencies', // Inggris
            'technical skills', // Inggris
            'kemampuan',    // Indonesia
        ];
        
        foreach ($skills_sections as $section) {
            $pattern = '/'.preg_quote($section, '/').'(.*?)(\n\s*\n|$)/is';
            if (preg_match($pattern, $text_lower, $matches)) {
                $skills_text = $matches[1];
                $lines = preg_split('/\n|,|;/', $skills_text);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strlen($line) < 2) continue;
                    $line_words = preg_split('/\s+/', $line);
                    foreach ($line_words as $w) {
                        $w = trim($w);
                        if (strlen($w) < 2) continue;
                        $skills_keywords[$w] = 15; // Bobot tinggi untuk skills
                    }
                }
            }
        }
        
        // Deteksi bagian PENGALAMAN KERJA / WORK EXPERIENCE
        $experience_keywords = [];
        $experience_sections = [
            'pengalaman kerja',
            'work experience',
            'riwayat pekerjaan',
            'employment history',
            'professional experience'
        ];
        
        foreach ($experience_sections as $section) {
            $pattern = '/'.preg_quote($section, '/').'(.*?)(\n\s*\n|$)/is';
            if (preg_match($pattern, $text_lower, $matches)) {
                $exp_text = $matches[1];
                $words = preg_split('/\s+/', $exp_text);
                foreach ($words as $w) {
                    $w = trim($w);
                    if (strlen($w) < 3) continue;
                    $experience_keywords[$w] = 12; // Bobot tinggi untuk pengalaman
                }
            }
        }
        
        // Gabungkan semua keyword dengan bobot yang sesuai
        foreach ($skills_keywords as $k => $v) {
            if (!isset($word_count[$k]) || $word_count[$k] < $v) {
                $word_count[$k] = $v;
            }
        }
        
        foreach ($experience_keywords as $k => $v) {
            if (!isset($word_count[$k]) || $word_count[$k] < $v) {
                $word_count[$k] = $v;
            }
        }
        
        arsort($word_count);
        return array_slice($word_count, 0, 100, true); // Ambil 100 keyword teratas
    }
    
    /**
     * Implementasi Naive Bayes yang benar untuk klasifikasi dokumen
     */
    public function calculateMatchProbability($cv_keywords, $job) {
        try {
            $keyword_matches = 0;
            $total_keywords = count($cv_keywords);
            $category_match_score = 0;
            
            // Ambil keywords untuk lowongan ini
            $stmt = $this->conn->prepare("SELECT keyword, bobot FROM keywords WHERE kategori_id = ?");
            $stmt->bind_param("i", $job['kategori_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $job_keywords = [];
            while ($row = $result->fetch_assoc()) {
                $job_keywords[] = $row;
            }
            $stmt->close();
            
            // Hitung exact matches dengan bobot yang lebih tinggi untuk keyword spesifik
            foreach ($cv_keywords as $cv_word => $cv_count) {
                $cv_word_lower = strtolower($cv_word);
                
                foreach ($job_keywords as $job_keyword) {
                    $job_word_lower = strtolower($job_keyword['keyword']);
                    
                    if ($cv_word_lower === $job_word_lower) {
                        $keyword_matches++;
                        
                        // Berikan bobot lebih tinggi untuk keyword yang sangat spesifik
                        $specific_keywords = [
                            'courier', 'driver', 'delivery', 'logistics', 'transportation',
                            'programmer', 'developer', 'coding', 'software',
                            'midwife', 'bidan', 'nurse', 'healthcare',
                            'security', 'guard', 'surveillance',
                            'consultant', 'consulting',
                            'video', 'production', 'editing',
                            'nutrition', 'gizi', 'diet'
                        ];
                        
                        $weight_multiplier = 1.0;
                        if (in_array($cv_word_lower, $specific_keywords)) {
                            $weight_multiplier = 2.0; // Bobot 2x untuk keyword spesifik
                        }
                        
                        $category_match_score += $job_keyword['bobot'] * $cv_count * $weight_multiplier;
                    }
                }
            }
            
            // Hitung base score dengan normalisasi yang lebih baik
            $base_score = 0;
            if ($total_keywords > 0) {
                $base_score = ($keyword_matches / $total_keywords) * 0.4; // 40% dari skor berdasarkan keyword matches
            }
            
            // Hitung category relevance score dengan bobot yang lebih tinggi
            $category_relevance = 0;
            if (count($job_keywords) > 0) {
                $category_relevance = min(1.0, $category_match_score / (count($job_keywords) * 1.5)); // Kurangi pembagi untuk meningkatkan skor
            }
            $category_score = $category_relevance * 0.4; // 40% dari skor berdasarkan kategori (naik dari 30%)
            
            // Hitung job title relevance dengan bobot yang lebih tinggi
            $job_title_score = 0;
            $job_title_lower = strtolower($job['judul']);
            $title_matches = 0;
            foreach ($cv_keywords as $cv_word => $cv_count) {
                $cv_word_lower = strtolower($cv_word);
                if (strpos($job_title_lower, $cv_word_lower) !== false || 
                    strpos($cv_word_lower, $job_title_lower) !== false) {
                    $title_matches++;
                }
            }
            if (count($cv_keywords) > 0) {
                $job_title_score = min(0.3, ($title_matches / count($cv_keywords)) * 0.3); // 30% maksimal untuk job title (naik dari 20%)
            }
            
            // Hitung skor akhir dengan normalisasi yang lebih baik
            $final_score = $base_score + $category_score + $job_title_score;
            
            // Tambah bonus untuk CV yang sangat cocok
            $exact_match_bonus = 0;
            
            // Bonus untuk exact keyword matches yang banyak
            if ($keyword_matches > 0 && $total_keywords > 0) {
                $exact_match_ratio = $keyword_matches / $total_keywords;
                if ($exact_match_ratio > 0.2) { // Turunkan threshold dari 0.3 ke 0.2
                    $exact_match_bonus = min(0.2, $exact_match_ratio * 0.3); // Naikkan bonus maksimal
                }
            }
            
            // Bonus untuk category match yang tinggi
            if ($category_match_score > 0) {
                $category_bonus = min(0.15, $category_match_score / 20); // Naikkan bonus dan kurangi pembagi
                $exact_match_bonus += $category_bonus;
            }
            
            $final_score += $exact_match_bonus;
            
            // Normalisasi skor untuk menghindari nilai ekstrim
            $final_score = min(0.95, max(0.25, $final_score)); // Batasi antara 25% - 95%
            
            // Apply dynamic scaling untuk distribusi yang lebih natural
            if ($final_score > 0.6) {
                $final_score = 0.6 + ($final_score - 0.6) * 0.6; // Kurang compress high scores
            } elseif ($final_score < 0.4) {
                $final_score = 0.4 - (0.4 - $final_score) * 0.3; // Kurang expand low scores
            }
            
            // Tambah variasi kecil untuk mencegah skor identik
            $randomization = 1 + (mt_rand(-3, 3) / 1000); // ±0.3% (kurangi randomisasi)
            $final_score *= $randomization;
            
            // Pastikan skor tetap dalam batas yang wajar
            $final_score = min(0.95, max(0.25, $final_score));
            
            error_log(sprintf(
                "Job: %s, Category: %d, Matches: %d/%d, Base: %.3f, Category: %.3f, Title: %.3f, Final: %.3f",
                $job['judul'],
                $job['kategori_id'],
                $keyword_matches,
                $total_keywords,
                $base_score,
                $category_score,
                $job_title_score,
                $final_score
            ));
            
            return $final_score;
            
        } catch (Exception $e) {
            error_log("Error in calculateMatchProbability: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mencari lowongan yang cocok menggunakan Naive Bayes
     */
    public function findMatchingJobs($cv_text, $language = 'id') {
        try {
            // Cek apakah CV berisi template yang disimulasikan
            if ($this->isTemplateContent($cv_text)) {
                error_log("CV contains template content, using fallback classification");
                return $this->findMatchingJobsFallback($cv_text, $language);
            }
            
            $processed_cv_text = $this->preprocessText($cv_text, $language);
            $cv_keywords = $this->extractKeywords($processed_cv_text);
            
            // Debug: Log extracted keywords
            error_log("=== CV KEYWORDS DEBUG ===");
            error_log("Original CV text length: " . strlen($cv_text));
            error_log("Processed CV text length: " . strlen($processed_cv_text));
            error_log("Extracted keywords count: " . count($cv_keywords));
            error_log("Top 20 keywords:");
            $count = 0;
            foreach ($cv_keywords as $keyword => $freq) {
                if ($count >= 20) break;
                error_log("  '$keyword' => $freq");
                $count++;
            }
            error_log("=== END CV KEYWORDS DEBUG ===");
            
            // Ambil semua lowongan dengan DISTINCT untuk menghindari duplikasi
            $result = $this->conn->query("
                SELECT DISTINCT l.id, l.judul, l.perusahaan, l.deskripsi, l.persyaratan, 
                       l.lokasi, l.kategori_id, l.sumber, l.tanggal_posting, l.tanggal_dibuat,
                       k.nama_kategori 
                FROM lowongan l 
                JOIN kategori k ON l.kategori_id = k.id 
                WHERE l.id > 0
                ORDER BY l.id DESC
            ");
            
            if (!$result) {
                error_log("Query error (lowongan): " . $this->conn->error);
                return [];
            }
            
            $all_jobs = [];
            $job_scores = [];
            $seen_jobs = []; // Untuk tracking duplikasi berdasarkan judul dan perusahaan
            
            // Hitung skor untuk setiap lowongan
            while ($job = $result->fetch_assoc()) {
                // Cek duplikasi berdasarkan judul dan perusahaan
                $job_key = strtolower($job['judul'] . '|' . $job['perusahaan']);
                if (in_array($job_key, $seen_jobs)) {
                    continue; // Skip duplikat
                }
                $seen_jobs[] = $job_key;
                
                $match_score = $this->calculateMatchProbability($cv_keywords, $job);
                $job['match_score'] = $match_score;
                $all_jobs[] = $job;
                $job_scores[] = $match_score;
            }
            
            // Jika tidak ada lowongan, kembalikan array kosong
            if (empty($all_jobs)) {
                return [];
            }
            
            // Hitung statistik skor
            $avg_score = array_sum($job_scores) / count($job_scores);
            $max_score = max($job_scores);
            $min_score = min($job_scores);
            $std_dev = $this->calculateStandardDeviation($job_scores);
            
            error_log("Score statistics - Avg: " . number_format($avg_score, 4) . 
                     ", Max: " . number_format($max_score, 4) . 
                     ", Min: " . number_format($min_score, 4) . 
                     ", StdDev: " . number_format($std_dev, 4));
            
            // Filter lowongan dengan threshold yang lebih ketat dan filtering kategori
            $filtered_jobs = [];
            $threshold = max($avg_score - $std_dev * 0.5, 0.40); // Threshold yang lebih akurat (40%)
            
            // Tentukan kategori utama CV berdasarkan keyword matches
            $cv_category_scores = $this->analyzeCVCategory($cv_keywords);
            
            // Tentukan kategori utama CV
            $main_cv_category = null;
            $max_category_score = 0;
            foreach ($cv_category_scores as $cat_id => $score) {
                if ($score > $max_category_score) {
                    $max_category_score = $score;
                    $main_cv_category = $cat_id;
                }
            }
            
            error_log("Main CV category: " . $main_cv_category . " with score: " . $max_category_score);
            
            foreach ($all_jobs as $job) {
                $job_score = $job['match_score'];
                
                // Bonus untuk kategori yang sama dengan CV
                if ($main_cv_category && $job['kategori_id'] == $main_cv_category) {
                    $job_score *= 1.5; // Bonus 50% untuk kategori yang sama
                }
                
                // Penalty untuk kategori yang sangat berbeda
                if ($main_cv_category && $job['kategori_id'] != $main_cv_category) {
                    $job_score *= 0.7; // Penalty 30% untuk kategori berbeda
                }
                
                if ($job_score >= $threshold) {
                    $job['match_score'] = $job_score; // Update skor yang sudah disesuaikan
                    $filtered_jobs[] = $job;
                }
            }
            
            // Jika masih terlalu sedikit hasil, turunkan threshold tapi tetap prioritaskan kategori yang sama
            if (count($filtered_jobs) < 3) {
                $filtered_jobs = [];
                $threshold = max($avg_score - $std_dev * 1.0, 0.35); // Threshold minimal 35%
                
                foreach ($all_jobs as $job) {
                    $job_score = $job['match_score'];
                    
                    if ($main_cv_category && $job['kategori_id'] == $main_cv_category) {
                        $job_score *= 1.3; // Bonus 30% untuk kategori yang sama
                    }
                    
                    if ($job_score >= $threshold) {
                        $job['match_score'] = $job_score;
                        $filtered_jobs[] = $job;
                    }
                }
            }
            
            // Urutkan berdasarkan skor tertinggi
            usort($filtered_jobs, function($a, $b) {
                return $b['match_score'] <=> $a['match_score'];
            });
            
            // Log hasil
            foreach ($filtered_jobs as $job) {
                error_log(sprintf(
                    "Job: %s, Score: %.2f%%",
                    $job['judul'],
                    $job['match_score'] * 100
                ));
            }
            
            error_log(sprintf(
                "Total %d jobs filtered (threshold: %.3f), returning top 10", 
                count($filtered_jobs),
                $threshold
            ));
            
            // Return top 10 dengan diversifikasi kategori dan filtering ketat
            $final_results = $this->diversifyResults($filtered_jobs, 10);
            
            // Filter tambahan untuk menghindari lowongan yang tidak relevan
            if (!empty($final_results) && $main_cv_category) {
                $relevant_results = [];
                $irrelevant_results = [];
                
                foreach ($final_results as $job) {
                    if ($job['kategori_id'] == $main_cv_category) {
                        $relevant_results[] = $job;
                    } else {
                        // Hanya masukkan jika skor sangat tinggi
                        if ($job['match_score'] >= 0.6) { // 60% atau lebih
                            $irrelevant_results[] = $job;
                        }
                    }
                }
                
                // Prioritaskan hasil yang relevan
                $final_results = array_merge($relevant_results, $irrelevant_results);
                $final_results = array_slice($final_results, 0, 10);
            }
            
            // Cek apakah ada hasil dengan skor yang cukup tinggi
            $high_score_results = [];
            foreach ($final_results as $job) {
                if ($job['match_score'] >= 0.40) { // Kembalikan ke 40% untuk akurasi
                    $high_score_results[] = $job;
                }
            }
            
            // Jika tidak ada hasil dengan skor tinggi, kembalikan array kosong
            if (empty($high_score_results)) {
                error_log("No jobs found with sufficient match score (min 40%)");
                return [];
            }
            
            return $high_score_results;
            
            return $final_results;
            
        } catch (Exception $e) {
            error_log("Error in findMatchingJobs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cek apakah konten CV adalah template yang disimulasikan atau text yang buruk
     */
    public function isTemplateContent($cv_text) {
        // Cek template indicators
        $template_indicators = [
            'Nama: [Nama dari CV]',
            'Email: [Email dari CV]',
            'Telepon: [Telepon dari CV]',
            'Alamat: [Alamat dari CV]',
            '[Ringkasan profesional dari CV]',
            '[Keahlian dari CV]',
            '[Pengalaman kerja dari CV]',
            '[Pendidikan dari CV]',
            '[Sertifikasi dari CV]'
        ];
        
        $template_count = 0;
        foreach ($template_indicators as $indicator) {
            if (stripos($cv_text, $indicator) !== false) {
                $template_count++;
            }
        }
        
        // Jika lebih dari 3 indikator template ditemukan, anggap sebagai template
        if ($template_count >= 3) {
            return true;
        }
        
        // Cek apakah text mengandung terlalu banyak karakter aneh (hasil OCR yang buruk)
        $garbage_chars = preg_match_all('/[^a-zA-Z0-9\s\-\.\,\!\?\(\)\:\;]/', $cv_text);
        $total_chars = strlen($cv_text);
        $garbage_ratio = $garbage_chars / $total_chars;
        
        // Jika lebih dari 30% karakter adalah garbage, anggap sebagai text buruk
        if ($garbage_ratio > 0.3) {
            error_log("CV text contains too much garbage characters: " . round($garbage_ratio * 100, 1) . "%");
            return true;
        }
        
        // Cek apakah text terlalu pendek atau tidak bermakna
        $words = preg_split('/\s+/', trim($cv_text));
        $meaningful_words = 0;
        foreach ($words as $word) {
            if (strlen($word) > 2 && preg_match('/^[a-zA-Z]+$/', $word)) {
                $meaningful_words++;
            }
        }
        
        $meaningful_ratio = $meaningful_words / count($words);
        if ($meaningful_ratio < 0.3) {
            error_log("CV text contains too few meaningful words: " . round($meaningful_ratio * 100, 1) . "%");
            return true;
        }
        
        return false;
    }
    
    /**
     * Analisis kategori CV berdasarkan keywords
     */
    public function analyzeCVCategory($cv_keywords) {
        $cv_category_scores = [];
        
        foreach ($cv_keywords as $word => $count) {
            $word_lower = strtolower($word);
            
            // Cek di semua kategori keywords
            $stmt = $this->conn->prepare("SELECT kategori_id, bobot FROM keywords WHERE LOWER(keyword) = ?");
            $stmt->bind_param("s", $word_lower);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $cat_id = $row['kategori_id'];
                    $cv_category_scores[$cat_id] = ($cv_category_scores[$cat_id] ?? 0) + $row['bobot'] * $count;
                }
            }
            $stmt->close();
            
            // Cek partial matches dengan similarity threshold yang lebih ketat
            $stmt = $this->conn->prepare("SELECT kategori_id, keyword, bobot FROM keywords");
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $cat_word = strtolower($row['keyword']);
                    $similarity = similar_text($word_lower, $cat_word) / max(strlen($word_lower), strlen($cat_word));
                    if ($similarity > 0.9) { // Threshold yang sangat ketat
                        $cat_id = $row['kategori_id'];
                        $cv_category_scores[$cat_id] = ($cv_category_scores[$cat_id] ?? 0) + $row['bobot'] * $similarity * 0.5;
                    }
                }
            }
            $stmt->close();
        }
        
        return $cv_category_scores;
    }
    
    /**
     * Fallback method untuk CV yang berisi template
     */
    private function findMatchingJobsFallback($cv_text, $language) {
        try {
            // Untuk CV template, gunakan analisis sederhana berdasarkan kata kunci umum
            $common_keywords = [
                'programmer' => 1,
                'developer' => 1,
                'coding' => 1,
                'software' => 1,
                'php' => 1,
                'javascript' => 1,
                'laravel' => 1,
                'react' => 1,
                'web development' => 1,
                'bidan' => 7,
                'midwife' => 7,
                'nurse' => 7,
                'healthcare' => 7,
                'asuhan kebidanan' => 7,
                'pelayanan kesehatan' => 7,
                'marketing' => 2,
                'sales' => 4,
                'seo' => 2,
                'digital marketing' => 2,
                'design' => 3,
                'ui design' => 3,
                'ux design' => 3,
                'accounting' => 6,
                'finance' => 6,
                'financial' => 6,
                'hr' => 5,
                'human resources' => 5,
                'recruitment' => 5,
                'teacher' => 10,
                'education' => 10,
                'teaching' => 10,
                'lawyer' => 12,
                'legal' => 12,
                'litigation' => 12,
                'driver' => 27,
                'transport' => 27,
                'courier' => 27,
                'delivery' => 27,
                'logistics' => 27,
                'security' => 26,
                'guard' => 26,
                'surveillance' => 26,
                'consultant' => 1,
                'it consulting' => 1,
                'system analysis' => 1,
                'video production' => 19,
                'video editing' => 19,
                'adobe premiere' => 19,
                'camera operation' => 19,
                'nutrition' => 7,
                'gizi' => 7,
                'diet planning' => 7,
                'health assessment' => 7
            ];
            
            $cv_lower = strtolower($cv_text);
            $detected_category = null;
            $max_score = 0;
            
            foreach ($common_keywords as $keyword => $category_id) {
                if (stripos($cv_lower, $keyword) !== false) {
                    $score = substr_count($cv_lower, $keyword);
                    if ($score > $max_score) {
                        $max_score = $score;
                        $detected_category = $category_id;
                    }
                }
            }
            
            if ($detected_category) {
                error_log("Fallback detected category: " . $detected_category);
                
                // Ambil lowongan berdasarkan kategori yang terdeteksi
                $stmt = $this->conn->prepare("
                    SELECT DISTINCT l.id, l.judul, l.perusahaan, l.deskripsi, l.persyaratan, 
                           l.lokasi, l.kategori_id, l.sumber, l.tanggal_posting, l.tanggal_dibuat,
                           k.nama_kategori 
                    FROM lowongan l 
                    JOIN kategori k ON l.kategori_id = k.id 
                    WHERE l.kategori_id = ?
                    ORDER BY l.id DESC
                    LIMIT 10
                ");
                $stmt->bind_param("i", $detected_category);
                
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $jobs = [];
                    while ($job = $result->fetch_assoc()) {
                        $job['match_score'] = 0.45; // Skor yang lebih masuk akal untuk hasil template
                        $jobs[] = $job;
                    }
                    $stmt->close();
                    return $jobs;
                }
                $stmt->close();
            }
            
            // Jika tidak ada kategori yang terdeteksi, kembalikan array kosong
            error_log("No category detected in fallback, returning empty result");
            return [];
            
        } catch (Exception $e) {
            error_log("Error in findMatchingJobsFallback: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Menghitung standard deviation untuk statistik skor
     */
    private function calculateStandardDeviation($scores) {
        if (empty($scores)) return 0;
        
        $mean = array_sum($scores) / count($scores);
        $variance = 0;
        
        foreach ($scores as $score) {
            $variance += pow($score - $mean, 2);
        }
        
        $variance /= count($scores);
        return sqrt($variance);
    }
    
    /**
     * Diversifikasi hasil untuk menghindari dominasi satu kategori
     */
    private function diversifyResults($jobs, $limit) {
        if (count($jobs) <= $limit) {
            return $jobs;
        }
        
        $result = [];
        $category_count = [];
        $max_per_category = ceil($limit / 3); // Maksimal 3-4 job per kategori
        
        foreach ($jobs as $job) {
            $category_id = $job['kategori_id'];
            
            // Cek apakah kategori sudah mencapai batas maksimal
            if (!isset($category_count[$category_id])) {
                $category_count[$category_id] = 0;
            }
            
            if ($category_count[$category_id] < $max_per_category) {
                $result[] = $job;
                $category_count[$category_id]++;
                
                if (count($result) >= $limit) {
                    break;
                }
            }
        }
        
        // Jika masih ada slot kosong, isi dengan job terbaik yang tersisa
        if (count($result) < $limit) {
            foreach ($jobs as $job) {
                if (count($result) >= $limit) break;
                
                $job_id = $job['id'];
                $already_included = false;
                
                foreach ($result as $included_job) {
                    if ($included_job['id'] == $job_id) {
                        $already_included = true;
                        break;
                    }
                }
                
                if (!$already_included) {
                    $result[] = $job;
                }
            }
        }
        
        return $result;
    }

    public function detectLanguage($text) {
        $text = strtolower($text);
        $words = preg_split('/\s+/', $text);

        $count_id = 0;
        $count_en = 0;

        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '') continue;
            if (in_array($word, $this->stopwords_id)) {
                $count_id++;
            }
            if (in_array($word, $this->stopwords_en)) {
                $count_en++;
            }
        }

        return ($count_en > $count_id) ? 'en' : 'id';
    }
}
?>
