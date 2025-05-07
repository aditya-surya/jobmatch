<?php
class NaiveBayes {
    private $conn;
    private $stopwords_id = [];
    private $stopwords_en = [];
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->loadStopwords();
    }
    
    /**
     * Load stopwords dari file
     */
    private function loadStopwords() {
        // Stopwords Bahasa Indonesia
        $stopwords_id_file = file_get_contents('data/stopwords_id.txt');
        if ($stopwords_id_file) {
            $this->stopwords_id = array_map('trim', explode("\n", $stopwords_id_file));
        }
        
        // Stopwords Bahasa Inggris
        $stopwords_en_file = file_get_contents('data/stopwords_en.txt');
        if ($stopwords_en_file) {
            $this->stopwords_en = array_map('trim', explode("\n", $stopwords_en_file));
        }
    }
    
    /**
     * Membersihkan dan memproses teks
     */
    public function preprocessText($text, $language = 'id') {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove special characters and numbers
        $text = preg_replace('/[^a-z\s]/i', ' ', $text);
        
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Tokenize
        $words = explode(' ', $text);
        
        // Remove stopwords
        $stopwords = ($language == 'id') ? $this->stopwords_id : $this->stopwords_en;
        $filtered_words = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if ($word != "" && !in_array($word, $stopwords) && strlen($word) > 2) {
                $filtered_words[] = $word;
            }
        }
        
        // Menggabungkan kembali menjadi teks
        return implode(' ', $filtered_words);
    }
    
    /**
     * Ekstrak kata kunci dari teks
     */
    public function extractKeywords($text) {
        $words = explode(' ', $text);
        $word_count = array_count_values($words);
        
        // Mengurutkan kata berdasarkan frekuensi (dari tinggi ke rendah)
        arsort($word_count);
        
        // Mengambil kata kunci (misalnya 50 kata teratas)
        $keywords = array_slice($word_count, 0, 50, true);
        
        return $keywords;
    }
    
    /**
     * Hitung probabilitas kecocokan dengan Naive Bayes
     */
    public function calculateMatchProbability($cv_keywords, $job) {
        // Ekstrak kata kunci dari deskripsi pekerjaan
        $job_text = $this->preprocessText($job['deskripsi'] . ' ' . $job['persyaratan']);
        $job_keywords = $this->extractKeywords($job_text);
        
        // Hitung total kata kunci yang cocok
        $total_matches = 0;
        $total_weight = 0;
        
        foreach ($cv_keywords as $keyword => $count) {
            // Cek jika kata kunci CV ada di kata kunci lowongan
            if (isset($job_keywords[$keyword]) || 
                strpos($job_text, $keyword) !== false) {
                
                // Cek apakah kata kunci ini merupakan skill atau kualifikasi penting
                $query = "SELECT bobot FROM keywords WHERE keyword LIKE ?";
                $stmt = $this->conn->prepare($query);
                $search_keyword = "%{$keyword}%";
                $stmt->bind_param("s", $search_keyword);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $weight = $row['bobot'];
                } else {
                    $weight = 1; // Bobot default
                }
                
                $total_matches += $count * $weight;
                $total_weight += $weight;
            }
        }
        
        // Hitung probabilitas (skor kecocokan)
        $match_probability = ($total_weight > 0) ? $total_matches / ($total_weight * 10) : 0;
        
        // Normalisasi skor kecocokan (0-1)
        return min(1, max(0, $match_probability));
    }
    
    /**
     * Cari lowongan yang cocok
     */
    public function findMatchingJobs($cv_text, $language = 'id') {
        // Preprocess CV text
        $processed_cv_text = $this->preprocessText($cv_text, $language);
        
        // Extract keywords from CV
        $cv_keywords = $this->extractKeywords($processed_cv_text);
        
        // Get all jobs from database
        $query = "SELECT * FROM lowongan 
                  JOIN kategori ON lowongan.kategori_id = kategori.id
                  ORDER BY lowongan.id DESC";
        $result = $this->conn->query($query);
        
        $matching_jobs = [];
        
        if ($result->num_rows > 0) {
            while ($job = $result->fetch_assoc()) {
                // Calculate match probability
                $match_score = $this->calculateMatchProbability($cv_keywords, $job);
                
                // Add match score to job
                $job['match_score'] = $match_score;
                
                // Only include jobs with match score above threshold (e.g., 0.3)
                if ($match_score >= 0.3) {
                    $matching_jobs[] = $job;
                }
            }
        }
        
        // Sort jobs by match score (highest first)
        usort($matching_jobs, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });
        
        // Limit results to top 15
        return array_slice($matching_jobs, 0, 15);
    }
}
?>
