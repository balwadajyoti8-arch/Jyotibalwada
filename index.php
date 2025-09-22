<?php
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === 0) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = basename($_FILES['csv_file']['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $targetFile)) {
            // Save uploaded file name in session for faculty.php
            $_SESSION['students_file'] = $filename;
            header('Location: faculty.php');
            exit;
        } else {
            $error = "Failed to save uploaded file.";
        }
    } else {
        $error = "Please upload a valid CSV file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Attainment Calculation System</title>

  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div class="main-container">
    <!-- Header Section -->
    <header class="header">
      <h1 class="main-title">Attainment Calculation System</h1>
      <div class="header-sub" style="color: black;">
        <a href="index.php" class="font-medium text-black-600 hover:text-blue-600">Admin Dashboard</a>
        <a href="login.php" class="font-medium text-black-600 hover:text-blue-600">Login></a>
        <a href="faculty.php" class="font-medium text-black-600 hover:text-blue-600">Faculty Interface</a>
        <a href="new.php" class ="font-medium text-black-600 hover:text-blue-600">Calculate CO</a>
      </div>
    </header>

    <!-- Admin Dashboard -->
    <section class="dashboard">
      <h2 class="dashboard-title">Admin Dashboard</h2>
      <p class="dashboard-subtext">Upload student data and manage assessment files</p>

      <!-- Upload Form -->
      <div class="upload-box">
        <?php if ($error): ?>
          <p class="text-red-600 font-bold mb-3"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
          <label class="upload-label">Upload Student CSV (Roll No., Name):</label>
          <input type="file" name="csv_file" accept=".csv" required class="file-input mb-3" /><br />
          <input type="submit" value="Upload CSV" class="upload-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 cursor-pointer" />
        </form>
        <p class="status-text">
          <?php 
            if (!empty($_SESSION['students_file'])) {
                echo "Uploaded: " . htmlspecialchars($_SESSION['students_file']);
            } else {
                echo "No uploads yet";
            }
          ?>
        </p>
      </div>

      <!-- Generated Files -->
      <div class="generated-box mt-6">
        <h3 class="generated-title">Generated Assessment Files</h3>
        <p class="status-text">No assessment files generated yet</p>
      </div>
    </section>
  </div>
</body>

</html>
