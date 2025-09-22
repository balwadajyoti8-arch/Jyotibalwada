<?php
session_start();

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// ====== Validate input ======
if (
    empty($_POST['students_file']) ||
    empty($_POST['course_name']) ||
    empty($_POST['assessment_type']) ||
    empty($_POST['part_question_map']) ||
    empty($_POST['question_co_map']) ||
    empty($_POST['max_marks'])
) {
    die("Missing required form data.");
}

// ====== Get and sanitize POST data ======
$studentsFile = basename($_POST['students_file']);
$courseName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $_POST['course_name']);
$assessmentType = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $_POST['assessment_type']);

$partQuestionMap = $_POST['part_question_map'];
$questionCoMap = $_POST['question_co_map'];
$maxMarks = $_POST['max_marks'];

// ====== Flatten mappings ======
$flatMaxMarks = [];
$flatQuestionCoMap = [];

foreach ($maxMarks as $part => $questions) {
    foreach ($questions as $question => $mark) {
        $flatMaxMarks[$question] = $mark;
    }
}
foreach ($questionCoMap as $part => $questions) {
    foreach ($questions as $question => $co) {
        $flatQuestionCoMap[$question] = $co;
    }
}

// ====== Load student CSV ======
$uploadDir = __DIR__ . '/uploads/';
$csvPath = $uploadDir . $studentsFile;

if (!file_exists($csvPath)) {
    die("Student CSV file not found.");
}

$students = [];
if (($handle = fopen($csvPath, 'r')) !== false) {
    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) >= 2) {
            $students[] = ['roll_no' => $data[0], 'name' => $data[1]];
        }
    }
    fclose($handle);
}

if (count($students) === 0) {
    die("No students found.");
}

// ====== Prepare question list ======
$allQuestions = [];
foreach ($partQuestionMap as $part => $questions) {
    foreach ($questions as $q) {
        $allQuestions[] = $q;
    }
}

// ====== Spreadsheet setup ======
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($assessmentType);

// Fixed columns
$sheet->setCellValue('A1', 'S.NO');
$sheet->setCellValue('B1', 'ROLL NO');
$sheet->setCellValue('C1', 'NAME OF STUDENT');

$fixedColsCount = 3;
$startColIndex = $fixedColsCount + 1;

// ====== Row 1: PARTS ======
$currentCol = $startColIndex;
foreach ($partQuestionMap as $part => $questions) {
    $start = $currentCol;
    $end = $currentCol + count($questions) - 1;
    $startLetter = Coordinate::stringFromColumnIndex($start);
    $endLetter = Coordinate::stringFromColumnIndex($end);

    if ($start !== $end) {
        $sheet->mergeCells("{$startLetter}1:{$endLetter}1");
    }

    $sheet->setCellValue("{$startLetter}1", $part);
    $sheet->getStyle("{$startLetter}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $currentCol += count($questions);
}

// ====== Row 2: Questions ======
$currentCol = $startColIndex;
foreach ($allQuestions as $q) {
    $letter = Coordinate::stringFromColumnIndex($currentCol);
    $sheet->setCellValue("{$letter}2", $q);
    $currentCol++;
}

// ====== Row 3: COs ======
$currentCol = $startColIndex;
foreach ($allQuestions as $q) {
    $letter = Coordinate::stringFromColumnIndex($currentCol);
    $sheet->setCellValue("{$letter}3", $flatQuestionCoMap[$q] ?? '');
    $currentCol++;
}

// ====== Row 4: Max Marks ======
$currentCol = $startColIndex;
foreach ($allQuestions as $q) {
    $letter = Coordinate::stringFromColumnIndex($currentCol);
    $sheet->setCellValue("{$letter}4", $flatMaxMarks[$q] ?? 0);
    $currentCol++;
}

// ====== Row 5: Min Qualifying (60%) ======
$currentCol = $startColIndex;
foreach ($allQuestions as $q) {
    $letter = Coordinate::stringFromColumnIndex($currentCol);
    $maxMark = floatval($flatMaxMarks[$q] ?? 0);
    $sheet->setCellValue("{$letter}5", round($maxMark * 0.6, 2));
    $currentCol++;
}

// ====== Row 6: Header Row ======
$sheet->setCellValue('A6', 'S.NO');
$sheet->setCellValue('B6', 'ROLL NO');
$sheet->setCellValue('C6', 'NAME OF STUDENT');

$currentCol = $startColIndex;
foreach ($allQuestions as $q) {
    $letter = Coordinate::stringFromColumnIndex($currentCol);
    $sheet->setCellValue("{$letter}6", $q);
    $currentCol++;
}

$totalColIndex = $startColIndex + count($allQuestions);
$totalColLetter = Coordinate::stringFromColumnIndex($totalColIndex);
$totalMax = array_sum(array_map('floatval', $flatMaxMarks));
$sheet->setCellValue("{$totalColLetter}6", "Total ({$totalMax})");

// ====== Student Data Rows ======
$rowNum = 7;
foreach ($students as $i => $student) {
    $sheet->setCellValue("A{$rowNum}", $i + 1);
    $sheet->setCellValue("B{$rowNum}", $student['roll_no']);
    $sheet->setCellValue("C{$rowNum}", $student['name']);

    $col = $startColIndex;
    foreach ($allQuestions as $q) {
        $cell = Coordinate::stringFromColumnIndex($col) . $rowNum;
        $sheet->setCellValue($cell, '');
        $col++;
    }

    $sheet->setCellValue("{$totalColLetter}{$rowNum}", '');
    $rowNum++;
}

// ====== Styling ======
$sheet->getStyle("A1:C6")->getFont()->setBold(true);
$sheet->getStyle("D1:{$totalColLetter}6")->getFont()->setBold(true);

// Borders and fill for better alignment visualization (optional)
$sheet->getStyle("A1:{$totalColLetter}6")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle("A1:{$totalColLetter}6")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');

// Auto-size columns
foreach (range('A', $totalColLetter) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ====== Output Excel ======
$filename = "{$courseName}_{$assessmentType}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
