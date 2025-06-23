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
            $job_text = $this->preprocessText($job['deskripsi'] . ' ' . $job['persyaratan']);
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
                $category_keywords[$row['keyword']] = $row['bobot'];
            }
            $stmt->close();
            
            $total_matches = 0;
            $total_weight = 0;
            $category_matches = 0;
            
            foreach ($cv_keywords as $keyword => $count) {
                $weight = 1;
                $is_category_match = false;
                
                foreach ($category_keywords as $cat_keyword => $cat_weight) {
                    if (strpos($keyword, $cat_keyword) !== false || 
                        strpos($cat_keyword, $keyword) !== false) {
                        $weight = $cat_weight * 2;
                        $is_category_match = true;
                        $category_matches++;
                        break;
                    }
                }
                
                if (!$is_category_match) {
                    $stmt = $this->conn->prepare("SELECT bobot FROM keywords WHERE keyword LIKE ?");
                    $search_keyword = "%{$keyword}%";
                    $stmt->bind_param("s", $search_keyword);
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        if ($data = $result->fetch_assoc()) {
                            $weight = $data['bobot'];
                        }
                    }
                    $stmt->close();
                }
                
                $total_matches += $count * $weight;
                $total_weight += $weight;
            }
            
            $base_probability = ($total_weight > 0) ? $total_matches / ($total_weight * 10) : 0;
            $category_factor = $category_matches > 0 ? (1 + ($category_matches / 10)) : 0.5;
            $match_probability = $base_probability * $category_factor;
            
            error_log(sprintf(
                "Match details - Job: %s, Base prob: %.2f, Category matches: %d, Final prob: %.2f",
                $job['judul'],
                $base_probability,
                $category_matches,
                $match_probability
            ));
            
            return min(1, max(0, $match_probability));
            
        } catch (Exception $e) {
            error_log("Error in calculateMatchProbability: " . $e->getMessage());
            return 0;
        }
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
            
            $matching_jobs = [];
            while ($job = $result->fetch_assoc()) {
                $match_score = $this->calculateMatchProbability($cv_keywords, $job);
                $job['match_score'] = $match_score;
                
                if ($match_score >= 0.2) { // Lowered threshold for testing
                    $matching_jobs[] = $job;
                }
            }
            
            // Sort by match score
            usort($matching_jobs, function($a, $b) {
                return $b['match_score'] <=> $a['match_score'];
            });
            
            error_log(sprintf("Found %d matching jobs above threshold", count($matching_jobs)));
            
            return array_slice($matching_jobs, 0, 10);
            
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
