<?php
require_once 'config/database.php';

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $judul = $_POST['judul'];
                $perusahaan = $_POST['perusahaan'];
                $deskripsi = $_POST['deskripsi'];
                $persyaratan = $_POST['persyaratan'];
                $lokasi = $_POST['lokasi'];
                $kategori_id = $_POST['kategori_id'];
                $sumber = $_POST['sumber'];
                $tanggal_posting = $_POST['tanggal_posting'];
                
                $stmt = $conn->prepare("INSERT INTO lowongan (judul, perusahaan, deskripsi, persyaratan, lokasi, kategori_id, sumber, tanggal_posting) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssiss", $judul, $perusahaan, $deskripsi, $persyaratan, $lokasi, $kategori_id, $sumber, $tanggal_posting);
                
                if ($stmt->execute()) {
                    $message = "Lowongan berhasil ditambahkan!";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $judul = $_POST['judul'];
                $perusahaan = $_POST['perusahaan'];
                $deskripsi = $_POST['deskripsi'];
                $persyaratan = $_POST['persyaratan'];
                $lokasi = $_POST['lokasi'];
                $kategori_id = $_POST['kategori_id'];
                $sumber = $_POST['sumber'];
                $tanggal_posting = $_POST['tanggal_posting'];
                
                $stmt = $conn->prepare("UPDATE lowongan SET judul=?, perusahaan=?, deskripsi=?, persyaratan=?, lokasi=?, kategori_id=?, sumber=?, tanggal_posting=? WHERE id=?");
                $stmt->bind_param("sssssissi", $judul, $perusahaan, $deskripsi, $persyaratan, $lokasi, $kategori_id, $sumber, $tanggal_posting, $id);
                
                if ($stmt->execute()) {
                    $message = "Lowongan berhasil diperbarui!";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM lowongan WHERE id=?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Lowongan berhasil dihapus!";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
        }
    }
}

// Get categories for dropdown
$categories = [];
$cat_result = $conn->query("SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori");
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row;
}

// Pagination settings
$items_per_page = 30;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get lowongan data for listing with pagination
$lowongan_list = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

$where_conditions = [];
$params = [];
$types = '';

if ($search) {
    $where_conditions[] = "(l.judul LIKE ? OR l.perusahaan LIKE ? OR l.lokasi LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($kategori_filter) {
    $where_conditions[] = "l.kategori_id = ?";
    $params[] = $kategori_filter;
    $types .= 'i';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$query = "SELECT l.*, k.nama_kategori 
          FROM lowongan l 
          LEFT JOIN kategori k ON l.kategori_id = k.id 
          $where_clause 
          ORDER BY l.tanggal_dibuat DESC 
          LIMIT $offset, $items_per_page";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

while ($row = $result->fetch_assoc()) {
    $lowongan_list[] = $row;
}

// Count total listings for pagination
$total_query = "SELECT COUNT(*) as total FROM lowongan l $where_clause";
if (!empty($params)) {
    $stmt_total = $conn->prepare($total_query);
    $stmt_total->bind_param($types, ...$params);
    $stmt_total->execute();
    $total_result = $stmt_total->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_items = $total_row['total'];
    $stmt_total->close();
} else {
    $total_result = $conn->query($total_query);
    $total_row = $total_result->fetch_assoc();
    $total_items = $total_row['total'];
}
$total_pages = ceil($total_items / $items_per_page);

// Get single lowongan for edit
$edit_lowongan = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM lowongan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    $edit_lowongan = $edit_result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - JobMatch</title>
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-left">
                <h1><i class="fas fa-briefcase"></i> Admin Panel JobMatch</h1>
                <p>Kelola Data Lowongan Pekerjaan</p>
            </div>
            <nav class="header-nav">
                <a href="?action=list" class="nav-link <?php echo $action == 'list' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Daftar Lowongan
                </a>
                <a href="?action=add" class="nav-link <?php echo $action == 'add' ? 'active' : ''; ?>">
                    <i class="fas fa-plus"></i> Tambah Lowongan
                </a>
            </nav>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <main class="admin-main">
            <?php if ($action == 'list'): ?>
                <!-- List Lowongan -->
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Daftar Lowongan</h2>
                    <div class="search-filter">
                        <form method="GET" action="" class="search-form">
                            <input type="hidden" name="action" value="list">
                            <input type="text" name="search" placeholder="Cari judul, perusahaan, atau lokasi..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" class="search-input">
                            <select name="kategori" class="filter-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </form>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Perusahaan</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Tanggal Posting</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lowongan_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data lowongan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lowongan_list as $index => $lowongan): ?>
                                    <tr>
                                        <td><?php echo $index + 1 + $offset; ?></td>
                                        <td>
                                            <div class="job-title">
                                                <strong><?php echo htmlspecialchars($lowongan['judul']); ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($lowongan['perusahaan']); ?></td>
                                        <td>
                                            <span class="category-badge">
                                                <?php echo htmlspecialchars($lowongan['nama_kategori']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($lowongan['lokasi']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($lowongan['tanggal_posting'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?action=edit&id=<?php echo $lowongan['id']; ?>" 
                                                   class="btn btn-sm btn-edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="confirmDelete(<?php echo $lowongan['id']; ?>, '<?php echo htmlspecialchars($lowongan['judul']); ?>')" 
                                                        class="btn btn-sm btn-delete" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?action=list&page=<?php echo $current_page - 1; ?>" class="btn btn-secondary" title="Sebelumnya">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?action=list&page=<?php echo $i; ?>" class="btn <?php echo $i == $current_page ? 'btn-primary active' : 'btn-secondary'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?action=list&page=<?php echo $current_page + 1; ?>" class="btn btn-secondary" title="Berikutnya">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>

            <?php elseif ($action == 'add' || $action == 'edit'): ?>
                <!-- Add/Edit Form -->
                <div class="section-header">
                    <h2>
                        <i class="fas fa-<?php echo $action == 'add' ? 'plus' : 'edit'; ?>"></i>
                        <?php echo $action == 'add' ? 'Tambah Lowongan Baru' : 'Edit Lowongan'; ?>
                    </h2>
                </div>

                <div class="form-container">
                    <form method="POST" action="" class="admin-form">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action == 'edit' && $edit_lowongan): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_lowongan['id']; ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="judul">Judul Pekerjaan *</label>
                                <input type="text" id="judul" name="judul" required
                                       value="<?php echo $edit_lowongan ? htmlspecialchars($edit_lowongan['judul']) : ''; ?>"
                                       placeholder="Contoh: Web Developer">
                            </div>
                            <div class="form-group">
                                <label for="perusahaan">Nama Perusahaan *</label>
                                <input type="text" id="perusahaan" name="perusahaan" required
                                       value="<?php echo $edit_lowongan ? htmlspecialchars($edit_lowongan['perusahaan']) : ''; ?>"
                                       placeholder="Contoh: PT Digital Solutions">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="kategori_id">Kategori *</label>
                                <select id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"
                                                <?php echo ($edit_lowongan && $edit_lowongan['kategori_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="lokasi">Lokasi *</label>
                                <input type="text" id="lokasi" name="lokasi" required
                                       value="<?php echo $edit_lowongan ? htmlspecialchars($edit_lowongan['lokasi']) : ''; ?>"
                                       placeholder="Contoh: Jakarta, Bandung, Surabaya">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="sumber">Sumber Lowongan *</label>
                                <select id="sumber" name="sumber" required>
                                    <option value="">Pilih Sumber</option>
                                    <option value="Jobstreet" <?php echo ($edit_lowongan && $edit_lowongan['sumber'] == 'Jobstreet') ? 'selected' : ''; ?>>Jobstreet</option>
                                    <option value="LinkedIn" <?php echo ($edit_lowongan && $edit_lowongan['sumber'] == 'LinkedIn') ? 'selected' : ''; ?>>LinkedIn</option>
                                    <option value="Glints" <?php echo ($edit_lowongan && $edit_lowongan['sumber'] == 'Glints') ? 'selected' : ''; ?>>Glints</option>
                                    <option value="Kalibrr" <?php echo ($edit_lowongan && $edit_lowongan['sumber'] == 'Kalibrr') ? 'selected' : ''; ?>>Kalibrr</option>
                                    <option value="Karir.com" <?php echo ($edit_lowongan && $edit_lowongan['sumber'] == 'Karir.com') ? 'selected' : ''; ?>>Karir.com</option>
                                    <option value="Urbanhire" <?php echo ($edit_lowongan && $edit_lowongan['sumber'] == 'Urbanhire') ? 'selected' : ''; ?>>Urbanhire</option>
                                    <option value="Techinasia" <?php echo ($edit_lowongan && $edit_lowongan['sumber'] == 'Techinasia') ? 'selected' : ''; ?>>Techinasia</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_posting">Tanggal Posting *</label>
                                <input type="date" id="tanggal_posting" name="tanggal_posting" required
                                       value="<?php echo $edit_lowongan ? $edit_lowongan['tanggal_posting'] : date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Pekerjaan *</label>
                            <textarea id="deskripsi" name="deskripsi" rows="6" required
                                      placeholder="Jelaskan deskripsi pekerjaan, tanggung jawab, dan benefit yang ditawarkan..."><?php echo $edit_lowongan ? htmlspecialchars($edit_lowongan['deskripsi']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="persyaratan">Persyaratan *</label>
                            <textarea id="persyaratan" name="persyaratan" rows="6" required
                                      placeholder="Sebutkan persyaratan pendidikan, pengalaman, skill, dan kualifikasi yang dibutuhkan..."><?php echo $edit_lowongan ? htmlspecialchars($edit_lowongan['persyaratan']) : ''; ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="?action=list" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $action == 'add' ? 'Simpan Lowongan' : 'Update Lowongan'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h3>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus lowongan "<span id="deleteJobTitle"></span>"?</p>
                <p class="warning">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteJobId">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id, title) {
            document.getElementById('deleteJobId').value = id;
            document.getElementById('deleteJobTitle').textContent = title;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto-hide alert messages
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 3000);
    </script>
</body>
</html> 
