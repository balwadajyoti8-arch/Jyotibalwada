<?php
require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

function readExcelWithMeta($path) {
    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);

    // Assuming:
    // Row 1: Parts (skip, not needed for calculation)
    // Row 2: Questions names (start col D)
    // Row 3: CO mapping (start col D)
    // Row 4: Max marks (start col D)
    // Row 5: Min qualifying marks (start col D)
    // Row 6 onwards: Student data (S.NO, Roll No, Name, then marks per question)

    $questions = [];
    $coMapping = [];
    $maxMarks = [];
    $thresholds = [];

    // Read columns starting from D (col 4)
    $startColIndex = 4;
    $highestCol = $sheet->getHighestColumn();
    $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

    for ($col = $startColIndex; $col <= $highestColIndex; $col++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

        $questions[] = $data[2][$colLetter] ?? '';   // Row 2: Questions
        $coMapping[] = $data[3][$colLetter] ?? '';   // Row 3: COs
        $maxMarks[] = floatval($data[4][$colLetter] ?? 0);  // Row 4: Max Marks
        $thresholds[] = floatval($data[5][$colLetter] ?? 0); // Row 5: Qualifying marks
    }

    // Read students data starting row 6
    $students = [];
    $row = 6;
    while (!empty($data[$row]['A'])) {  // until S.NO column empty
        $rowData = $data[$row];
        $students[] = $rowData;
        $row++;
    }

    return [$students, $questions, $coMapping, $maxMarks, $thresholds];
}

function processAttainment($students, $coMapping, $maxMarks, $thresholds) {
    $allCOs = array_unique(array_filter($coMapping));
    sort($allCOs);

    $totalStudents = count($students);
    $results = [];

    // Map CO => question indices
    $coQuestionIndices = [];
    foreach ($coMapping as $index => $co) {
        if ($co !== '') {
            $coQuestionIndices[$co][] = $index;
        }
    }

    foreach ($allCOs as $co) {
        $indices = $coQuestionIndices[$co];
        $sigmaSum = 0;
        $totalMM = 0;

        foreach ($students as $student) {
            $studentSum = 0;
            foreach ($indices as $idx) {
                // Question column in excel = idx + 4 (starting at D=4)
                $colIndex = $idx + 4;
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $cellVal = $student[$colLetter];

                // Only count if numeric and >= threshold
                $mark = is_numeric($cellVal) ? floatval($cellVal) : 0;

                if ($mark >= $thresholds[$idx]) {
                    $studentSum += $mark;
                }
            }
            $sigmaSum += $studentSum;
        }

        // Total max marks for CO across all questions
        $totalMM = 0;
        foreach ($indices as $idx) {
            $totalMM += $maxMarks[$idx];
        }
        $totalPossible = $totalMM * $totalStudents;

        $attainmentPercent = ($totalPossible > 0) ? ($sigmaSum / $totalPossible) * 100 : 0;

        $level = ($attainmentPercent < 40) ? "Low" : (($attainmentPercent < 70) ? "Medium" : "High");

        $results[] = [
            "CO" => $co,
            "Attainment (%)" => round($attainmentPercent, 2),
            "Level" => $level
        ];
    }

    return $results;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CO Attainment Calculator</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .upload-box { padding: 20px; border: 2px dashed #aaa; width: 400px; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 50%; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background: #eee; }
    </style>
</head>
<body>
<h2>📊 CO Attainment Calculator</h2>

<form method="post" enctype="multipart/form-data">
    <div class="upload-box">
        <label>Upload Filled Excel Sheet:</label><br><br>
        <input type="file" name="excelFile" accept=".xlsx,.xls" required>
        <br><br>
        <button type="submit" name="submit">Calculate</button>
    </div>
</form>

<?php
if (isset($_POST['submit']) && isset($_FILES['excelFile'])) {
    $fileTmpPath = $_FILES['excelFile']['tmp_name'];

    if (!empty($fileTmpPath)) {
        try {
            list($students, $questions, $coMapping, $maxMarks, $thresholds) = readExcelWithMeta($fileTmpPath);
            $results = processAttainment($students, $coMapping, $maxMarks, $thresholds);

            echo "<h3>CO Attainment Results:</h3>";
            echo "<table>";
            echo "<tr><th>CO</th><th>Attainment (%)</th><th>Level</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>{$row['CO']}</td>";
                echo "<td>{$row['Attainment (%)']}</td>";
                echo "<td>{$row['Level']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            echo "<p style='color:red;'>Error reading Excel file: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Error: Please upload a valid Excel file.</p>";
    }
}
?>

</body>
</html>
