<?php
// === DB CONFIG ===

// DB_CONNECTION=mysql
// DB_HOST=localhost
// DB_PORT=3306
// DB_DATABASE=s3487_tall_beach
// DB_USERNAME=u3487_tall_beach
// DB_PASSWORD=mP6jFUpiaMhWAFGj

$host = 'localhost';
$db   = 'u3487_tall_beach';
$user = 'u3487_tall_beach';
$pass = 'mP6jFUpiaMhWAFGj'
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // === CREATE TABLE IF NOT EXISTS ===
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS uploads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
} catch (\PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// === UPLOAD HANDLER ===
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    $title = $_POST['title'] ?? 'Untitled';
    $file = $_FILES['media'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid() . '_' . basename($file['name']);
        $target = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO uploads (title, filename) VALUES (?, ?)");
            $stmt->execute([$title, $filename]);
            $message = "✅ Upload successful!";
        } else {
            $message = "❌ Error moving uploaded file.";
        }
    } else {
        $message = "❌ Upload error: " . $file['error'];
    }
}

// === FETCH MEDIA ===
$media = $pdo->query("SELECT * FROM uploads ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Media Upload</title>
</head>
<body>
<h2>Upload Media</h2>
<?php if (!empty($message)) echo "<p><strong>$message</strong></p>"; ?>

<form action="" method="post" enctype="multipart/form-data">
    Title: <input type="text" name="title" required><br><br>
    File: <input type="file" name="media" required><br><br>
    <button type="submit">Upload</button>
</form>

<hr>
<h2>Uploaded Media</h2>
<?php foreach ($media as $item): ?>
    <div>
        <strong><?= htmlspecialchars($item['title']) ?></strong><br>
        <img src="uploads/<?= htmlspecialchars($item['filename']) ?>" alt="" style="max-width:200px"><br><br>
    </div>
<?php endforeach; ?>
</body>
</html>

