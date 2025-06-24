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
        return array_slice($word_count, 0, 50, true);
    }
    
    public function calculateMatchProbability($cv_keywords, $job) {
        try {
            $job_text = $this->preprocessText($job['deskripsi'] . ' ' . $job['persyaratan'] . ' ' . $job['judul']);
            $job_keywords = $this->extractKeywords($job_text);
            
            // Get job category keywords using MySQLi
            $stmt = $this->conn->prepare("SELECT keyword, bobot FROM keywords WHERE kategori_id = ?");
            $stmt->bind_param("i", $job['kategori_id']);
            if (!$stmt->execute()) {
                error_log("Query error (kategori_id): " . $stmt->error);
                return 0;
            }
            $result = $stmt->get_result();
            $category_keywords = [];
            while ($row = $result->fetch_assoc()) {
                $category_keywords[strtolower($row['keyword'])] = $row['bobot'];
            }
            $stmt->close();
            
            $total_score = 0;
            $max_possible_score = 0;
            $keyword_matches = 0;
            $high_value_matches = 0;
            $job_title_bonus = 0;
            
            // Check job title relevance first
            $job_title_lower = strtolower($job['judul']);
            foreach ($cv_keywords as $cv_keyword => $cv_count) {
                $cv_keyword_lower = strtolower(trim($cv_keyword));
                if (strlen($cv_keyword_lower) < 3) continue;
                
                // Strong job title match gets high bonus
                if (strpos($job_title_lower, $cv_keyword_lower) !== false || 
                    strpos($cv_keyword_lower, $job_title_lower) !== false ||
                    similar_text($cv_keyword_lower, $job_title_lower) / max(strlen($cv_keyword_lower), strlen($job_title_lower)) > 0.6) {
                    $job_title_bonus += 15 * $cv_count;
                }
            }
            
            // Calculate CV keyword relevance
            foreach ($cv_keywords as $cv_keyword => $cv_count) {
                $cv_keyword = strtolower(trim($cv_keyword));
                if (strlen($cv_keyword) < 3) continue;
                
                $keyword_score = 0;
                $keyword_weight = 1;
                
                // Check exact match in category keywords
                if (isset($category_keywords[$cv_keyword])) {
                    $keyword_score = $category_keywords[$cv_keyword] * 12;
                    $keyword_weight = $category_keywords[$cv_keyword];
                    $keyword_matches++;
                    if ($keyword_weight >= 1.3) {
                        $high_value_matches++;
                    }
                } else {
                    // Check partial matches in category keywords
                    foreach ($category_keywords as $cat_keyword => $cat_weight) {
                        $similarity = similar_text($cv_keyword, $cat_keyword) / max(strlen($cv_keyword), strlen($cat_keyword));
                        if (strpos($cv_keyword, $cat_keyword) !== false || 
                            strpos($cat_keyword, $cv_keyword) !== false ||
                            $similarity > 0.7) {
                            $keyword_score = $cat_weight * (8 + $similarity * 4);
                            $keyword_weight = $cat_weight;
                            $keyword_matches++;
                            if ($cat_weight >= 1.3) {
                                $high_value_matches++;
                            }
                            break;
                        }
                    }
                }
                
                // Check if keyword appears in job description/requirements
                if ($keyword_score == 0) {
                    foreach ($job_keywords as $job_keyword => $job_count) {
                        $similarity = similar_text($cv_keyword, $job_keyword) / max(strlen($cv_keyword), strlen($job_keyword));
                        if (strpos($cv_keyword, $job_keyword) !== false || 
                            strpos($job_keyword, $cv_keyword) !== false ||
                            $similarity > 0.8) {
                            $keyword_score = 2 + $similarity * 3;
                            $keyword_weight = 0.5;
                            break;
                        }
                    }
                }
                
                // Apply frequency bonus
                $frequency_bonus = min(2, $cv_count / 2);
                $total_score += $keyword_score * $frequency_bonus;
                $max_possible_score += 12 * $keyword_weight * $frequency_bonus;
            }
            
            // Add job title bonus to total score
            $total_score += $job_title_bonus;
            $max_possible_score += $job_title_bonus * 1.5; // Adjust max score accordingly
            
            // Hitung skor dasar
            $base_match = $max_possible_score > 0 ? ($total_score / $max_possible_score) : 0;
            
            // Terapkan bonus kategori (1.0 - 2.0)
            $category_factor = $this->getCategoryRelevanceFactor($cv_keywords, $job['kategori_id']);
            
            // Terapkan penalti ketidaksesuaian bidang (0.3 - 1.0)
            $field_penalty = $this->getFieldMismatchPenalty($cv_keywords, $job['kategori_id']);
            
            // Bonus tambahan untuk kecocokan tinggi
            $bonus_multiplier = 1.0;
            if ($base_match > 0.5) {
                // Tambah bonus 20% untuk setiap 10% di atas 50%
                $bonus_multiplier += (($base_match - 0.5) * 2);
            }
            
            // Kalkulasi skor akhir
            $final_score = $base_match * $category_factor * $field_penalty * $bonus_multiplier;
            
            // Tambah variasi kecil (±2%) untuk mencegah skor identik
            $randomization = 1 + (mt_rand(-2, 2) / 100);
            $final_score *= $randomization;
            
            // Pastikan skor antara 0 dan 1
            $final_score = min(1, max(0, $final_score));
            
            // Sesuaikan skala skor untuk lebih realistis
            if ($final_score > 0.75) {
                // Skor tinggi (>75%) dibiarkan apa adanya
                $final_score = $final_score;
            } elseif ($final_score > 0.5) {
                // Skor menengah (50-75%) sedikit dinaikkan
                $final_score = $final_score * 1.1;
            } else {
                // Skor rendah (<50%) sedikit diturunkan
                $final_score = $final_score * 0.9;
            }
            
            // Pastikan lagi skor tetap antara 0 dan 1
            $final_score = min(1, max(0, $final_score));
            
            error_log(sprintf(
                "Match details - Job: %s, Category: %d, Title bonus: %.1f, Keyword matches: %d, Base: %.3f, Category factor: %.2f, Field penalty: %.2f, Final: %.3f",
                $job['judul'],
                $job['kategori_id'],
                $job_title_bonus,
                $keyword_matches,
                $base_match,
                $category_factor,
                $field_penalty,
                $final_score
            ));
            
            return $final_score;
            
        } catch (Exception $e) {
            error_log("Error in calculateMatchProbability: " . $e->getMessage());
            return 0;
        }
    }
    
    private function getCategoryRelevanceFactor($cv_keywords, $category_id) {
        // Ambil semua keyword untuk kategori ini
        $stmt = $this->conn->prepare("SELECT keyword, bobot FROM keywords WHERE kategori_id = ?");
        $stmt->bind_param("i", $category_id);
        if (!$stmt->execute()) {
            return 1.0; // Default jika query gagal
        }
        $result = $stmt->get_result();
        $category_keywords = [];
        while ($row = $result->fetch_assoc()) {
            $category_keywords[strtolower($row['keyword'])] = $row['bobot'];
        }
        $stmt->close();
        
        if (empty($category_keywords)) {
            return 1.0;
        }
        
        $total_relevance = 0;
        $matched_keywords = 0;
        $high_value_matches = 0;
        
        // Hitung relevansi berdasarkan keyword dan bobotnya
        foreach ($cv_keywords as $cv_keyword => $count) {
            $cv_keyword = strtolower($cv_keyword);
            $best_match_score = 0;
            
            foreach ($category_keywords as $cat_keyword => $weight) {
                // Cek kecocokan exact atau partial
                if ($cv_keyword === $cat_keyword) {
                    // Exact match mendapat full weight
                    $best_match_score = max($best_match_score, $weight * 2);
                    if ($weight >= 1.3) $high_value_matches++;
                } elseif (strpos($cv_keyword, $cat_keyword) !== false || 
                         strpos($cat_keyword, $cv_keyword) !== false) {
                    // Partial match mendapat 70% weight
                    $best_match_score = max($best_match_score, $weight * 1.4);
                    if ($weight >= 1.3) $high_value_matches++;
                } else {
                    // Cek similarity untuk kata-kata yang mirip
                    $similarity = similar_text($cv_keyword, $cat_keyword) / max(strlen($cv_keyword), strlen($cat_keyword));
                    if ($similarity > 0.8) {
                        $best_match_score = max($best_match_score, $weight * $similarity * 1.3);
                        if ($weight >= 1.3) $high_value_matches++;
                    }
                }
            }
            
            if ($best_match_score > 0) {
                $total_relevance += $best_match_score;
                $matched_keywords++;
            }
        }
        
        // Hitung faktor kategori
        $match_ratio = $matched_keywords / count($cv_keywords);
        $base_factor = 1.0 + ($match_ratio * 0.5); // 1.0 - 1.5 berdasarkan rasio kecocokan
        
        // Tambah bonus untuk high-value matches
        $high_value_bonus = $high_value_matches * 0.1; // 0.1 bonus per high-value match
        
        // Faktor final antara 1.0 - 2.0
        $final_factor = min(2.0, $base_factor + $high_value_bonus);
        
        error_log(sprintf(
            "Category %d relevance: %.2f (matched: %d, high-value: %d, ratio: %.2f)",
            $category_id,
            $final_factor,
            $matched_keywords,
            $high_value_matches,
            $match_ratio
        ));
        
        return $final_factor;
    }
    
    private function getFieldMismatchPenalty($cv_keywords, $category_id) {
        // Definisikan keyword spesifik untuk setiap bidang
        $field_indicators = [
            1 => [ // IT
                'primary' => ['programming', 'developer', 'software', 'code', 'database', 'web', 'system'],
                'secondary' => ['app', 'tech', 'computer', 'application', 'development', 'frontend', 'backend']
            ],
            2 => [ // Marketing
                'primary' => ['marketing', 'digital', 'brand', 'promotion', 'advertising'],
                'secondary' => ['social media', 'seo', 'content', 'campaign', 'market']
            ],
            3 => [ // Design
                'primary' => ['design', 'ui', 'ux', 'graphic', 'visual'],
                'secondary' => ['creative', 'photoshop', 'illustrator', 'layout', 'artwork']
            ],
            4 => [ // Sales
                'primary' => ['sales', 'selling', 'account', 'business development'],
                'secondary' => ['customer', 'revenue', 'target', 'negotiation', 'client']
            ],
            5 => [ // Customer Service
                'primary' => ['customer service', 'support', 'help desk'],
                'secondary' => ['call center', 'client', 'customer care', 'service']
            ],
            6 => [ // Finance
                'primary' => ['finance', 'accounting', 'financial', 'accountant'],
                'secondary' => ['budget', 'audit', 'tax', 'bookkeeping', 'reconciliation']
            ],
            7 => [ // HR
                'primary' => ['human resources', 'hr', 'recruitment', 'hiring'],
                'secondary' => ['employee', 'payroll', 'talent', 'personnel', 'training']
            ],
            8 => [ // Engineering
                'primary' => ['engineering', 'engineer', 'mechanical', 'electrical'],
                'secondary' => ['technical', 'civil', 'construction', 'maintenance']
            ],
            9 => [ // Administration
                'primary' => ['administration', 'admin', 'secretary', 'administrative'],
                'secondary' => ['office', 'clerical', 'filing', 'documentation']
            ],
            10 => [ // Education
                'primary' => ['education', 'teaching', 'teacher', 'instructor'],
                'secondary' => ['training', 'academic', 'school', 'course', 'learning']
            ],
            11 => [ // Healthcare
                'primary' => ['medical', 'nurse', 'doctor', 'healthcare', 'bidan', 'midwife'],
                'secondary' => ['patient', 'clinical', 'hospital', 'health', 'perawat']
            ]
        ];
        
        if (!isset($field_indicators[$category_id])) {
            return 1.0; // Tidak ada penalti jika kategori tidak terdefinisi
        }
        
        $primary_keywords = $field_indicators[$category_id]['primary'];
        $secondary_keywords = $field_indicators[$category_id]['secondary'];
        
        $primary_matches = 0;
        $secondary_matches = 0;
        $total_cv_keywords = count($cv_keywords);
        
        // Cek kecocokan keyword CV dengan bidang pekerjaan
        foreach ($cv_keywords as $cv_keyword => $count) {
            $cv_keyword_lower = strtolower($cv_keyword);
            
            // Cek primary keywords (lebih penting)
            foreach ($primary_keywords as $field_keyword) {
                if ($this->isKeywordMatch($cv_keyword_lower, $field_keyword)) {
                    $primary_matches += ($count * 2); // Primary match bernilai 2x
                    break;
                }
            }
            
            // Cek secondary keywords
            foreach ($secondary_keywords as $field_keyword) {
                if ($this->isKeywordMatch($cv_keyword_lower, $field_keyword)) {
                    $secondary_matches += $count;
                    break;
                }
            }
        }
        
        // Hitung rasio kecocokan bidang
        $total_matches = $primary_matches + $secondary_matches;
        $field_alignment = $total_cv_keywords > 0 ? $total_matches / ($total_cv_keywords * 3) : 0;
        
        // Terapkan penalti berdasarkan tingkat ketidaksesuaian
        if ($field_alignment < 0.1) {
            $penalty = 0.3; // Penalti berat untuk bidang yang sangat tidak sesuai
        } elseif ($field_alignment < 0.2) {
            $penalty = 0.5; // Penalti sedang untuk bidang yang cukup berbeda
        } elseif ($field_alignment < 0.3) {
            $penalty = 0.7; // Penalti ringan untuk bidang yang agak berbeda
        } elseif ($field_alignment < 0.4) {
            $penalty = 0.85; // Penalti sangat ringan untuk bidang yang sedikit berbeda
        } else {
            $penalty = 1.0; // Tidak ada penalti untuk bidang yang sesuai
        }
        
        error_log(sprintf(
            "Field alignment for category %d: %.2f (primary: %d, secondary: %d, penalty: %.2f)",
            $category_id,
            $field_alignment,
            $primary_matches,
            $secondary_matches,
            $penalty
        ));
        
        return $penalty;
    }
    
    private function isKeywordMatch($cv_keyword, $field_keyword) {
        // Exact match
        if ($cv_keyword === $field_keyword) {
            return true;
        }
        
        // Partial match
        if (strpos($cv_keyword, $field_keyword) !== false || 
            strpos($field_keyword, $cv_keyword) !== false) {
            return true;
        }
        
        // Similarity match
        $similarity = similar_text($cv_keyword, $field_keyword) / max(strlen($cv_keyword), strlen($field_keyword));
        if ($similarity > 0.8) {
            return true;
        }
        
        return false;
    }
    
    public function findMatchingJobs($cv_text, $language = 'id') {
        try {
            $processed_cv_text = $this->preprocessText($cv_text, $language);
            $cv_keywords = $this->extractKeywords($processed_cv_text);
            
            // Using MySQLi for query
            $result = $this->conn->query("
                SELECT l.*, k.nama_kategori 
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
            $total_relevance = 0;
            
            while ($job = $result->fetch_assoc()) {
                $match_score = $this->calculateMatchProbability($cv_keywords, $job);
                $job['match_score'] = $match_score;
                $all_jobs[] = $job;
                $total_relevance += $match_score;
            }
            
            // Check if CV is completely unrelated to any jobs in database
            $average_relevance = count($all_jobs) > 0 ? $total_relevance / count($all_jobs) : 0;
            
            // If average relevance is extremely low (less than 5%), consider CV unrelated
            if ($average_relevance < 0.05) {
                error_log("CV appears to be unrelated to available job categories (avg relevance: " . ($average_relevance * 100) . "%)");
                return []; // This will trigger the "no matching jobs" message
            }
            
            // Urutkan semua lowongan berdasarkan skor
            usort($all_jobs, function($a, $b) {
                return $b['match_score'] <=> $a['match_score'];
            });
            
            // Log skor untuk debugging
            foreach ($all_jobs as $job) {
                error_log(sprintf(
                    "Job: %s, Score: %.2f%%",
                    $job['judul'],
                    $job['match_score'] * 100
                ));
            }
            
            error_log(sprintf(
                "Total %d jobs found (avg relevance: %.1f%%)", 
                count($all_jobs),
                $average_relevance * 100
            ));
            
            // Ambil 10 lowongan teratas
            return array_slice($all_jobs, 0, 10);
            
        } catch (Exception $e) {
            error_log("Error in findMatchingJobs: " . $e->getMessage());
            return [];
        }
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
