<?php
// faculty.php
session_start();

// Load uploaded CSVs to populate student_data select (adjust your upload folder path)
$uploadDir = __DIR__ . '/uploads/';
$studentFiles = [];
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if (preg_match('/\.csv$/i', $file)) {
            $studentFiles[] = $file;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Faculty Interface - Attainment Calculation</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <style>
    /* Add minor spacing */
    .part-block { margin-bottom: 1.5rem; padding: 1rem; border: 1px solid #ccc; border-radius: 8px; background:#f9f9f9;}
  </style>
</head>
<body class="bg-gray-100 p-8">

  <h1 class="text-3xl font-bold mb-6">Faculty Interface - Generate Excel</h1>

  <form id="facultyForm" action="generate_excel.php" method="POST" class="space-y-8">

    <!-- Select student CSV -->
    <div>
      <label for="students_file" class="block font-semibold mb-1">Select Uploaded Student CSV:</label>
      <select name="students_file" id="students_file" required class="border p-2 rounded w-full max-w-md">
        <option value="">-- Select CSV File --</option>
        <?php foreach ($studentFiles as $file): ?>
          <option value="<?= htmlspecialchars($file) ?>"><?= htmlspecialchars($file) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Course Name -->
    <div>
      <label for="course_name" class="block font-semibold mb-1">Course Name:</label>
      <input type="text" name="course_name" id="course_name" required class="border p-2 rounded w-full max-w-md" placeholder="Data Structures" />
    </div>

    <!-- Assessment Type -->
    <div>
      <label for="assessment_type" class="block font-semibold mb-1">Assessment Type:</label>
      <input type="text" name="assessment_type" id="assessment_type" required class="border p-2 rounded w-full max-w-md" placeholder="Mid-Term 1" />
    </div>

    <!-- Number of Parts -->
    <div>
      <label for="num_parts" class="block font-semibold mb-1">Number of Parts (e.g. 3):</label>
      <input type="number" min="1" max="10" id="num_parts" class="border p-2 rounded w-20" value="1" />
      <button type="button" id="generateParts" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded">Generate Parts</button>
    </div>

    <div id="partsContainer"></div>

    <!-- Submit -->
    <div>
      <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded font-semibold hover:bg-green-700">Generate Excel</button>
    </div>

  </form>

  <script>
    const partsContainer = document.getElementById('partsContainer');
    const generatePartsBtn = document.getElementById('generateParts');

    generatePartsBtn.addEventListener('click', () => {
      const numParts = parseInt(document.getElementById('num_parts').value);
      partsContainer.innerHTML = ''; // Clear

      for (let i = 1; i <= numParts; i++) {
        // Create block for each part
        const partDiv = document.createElement('div');
        partDiv.classList.add('part-block');

        // Part name input
        const partLabel = document.createElement('label');
        partLabel.textContent = `Part ${i} Name:`;
        partLabel.classList.add('block', 'font-semibold', 'mb-1');
        partLabel.htmlFor = `part_name_${i}`;

        const partNameInput = document.createElement('input');
        partNameInput.type = 'text';
        partNameInput.name = `part_names[${i}]`;
        partNameInput.id = `part_name_${i}`;
        partNameInput.placeholder = `e.g. Part A`;
        partNameInput.required = true;
        partNameInput.classList.add('border', 'p-2', 'rounded', 'w-full', 'max-w-md', 'mb-4');

        // Number of questions in this part
        const numQLabel = document.createElement('label');
        numQLabel.textContent = `Number of Questions in Part ${i}:`;
        numQLabel.classList.add('block', 'font-semibold', 'mb-1');
        numQLabel.htmlFor = `num_questions_${i}`;

        const numQInput = document.createElement('input');
        numQInput.type = 'number';
        numQInput.min = 1;
        numQInput.max = 20;
        numQInput.id = `num_questions_${i}`;
        numQInput.classList.add('border', 'p-2', 'rounded', 'w-20', 'mb-4');
        numQInput.value = 1;

        // Container for questions
        const questionsContainer = document.createElement('div');
        questionsContainer.id = `questions_container_${i}`;

        numQInput.addEventListener('change', () => {
          const qCount = parseInt(numQInput.value);
          questionsContainer.innerHTML = '';

          for (let q = 1; q <= qCount; q++) {
            // Question block
            const qDiv = document.createElement('div');
            qDiv.classList.add('mb-4');

            // Question label & input (editable question name, e.g. Q3/Q4)
            const qLabel = document.createElement('label');
            qLabel.textContent = `Question ${q} Name:`;
            qLabel.classList.add('block', 'mb-1');
            qLabel.htmlFor = `question_name_${i}_${q}`;

            const qInput = document.createElement('input');
            qInput.type = 'text';
            qInput.name = `part_question_map[${partNameInput.value || 'Part_'+i}][]`; // Use part name as key
            qInput.id = `question_name_${i}_${q}`;
            qInput.required = true;
            qInput.placeholder = 'e.g. Q3 or Q3/Q4';
            qInput.classList.add('border', 'p-2', 'rounded', 'w-40', 'mr-4');

            // Course Outcome for question
            const coLabel = document.createElement('label');
            coLabel.textContent = 'CO:';
            coLabel.classList.add('mr-2');

            const coInput = document.createElement('input');
            coInput.type = 'text';
            coInput.name = `question_co_map[${partNameInput.value || 'Part_'+i}][${qInput.value || 'Q'+q}]`;
            coInput.id = `question_co_${i}_${q}`;
            coInput.required = true;
            coInput.placeholder = 'e.g. CO1';
            coInput.classList.add('border', 'p-2', 'rounded', 'w-20', 'mr-4');

            // Max marks for question
            const maxMarkLabel = document.createElement('label');
            maxMarkLabel.textContent = 'Max Marks:';
            maxMarkLabel.classList.add('mr-2');

            const maxMarkInput = document.createElement('input');
            maxMarkInput.type = 'number';
            maxMarkInput.min = 0;
            maxMarkInput.name = `max_marks[${partNameInput.value || 'Part_'+i}][${qInput.value || 'Q'+q}]`;
            maxMarkInput.id = `max_marks_${i}_${q}`;
            maxMarkInput.required = true;
            maxMarkInput.placeholder = 'e.g. 2';
            maxMarkInput.classList.add('border', 'p-2', 'rounded', 'w-20');

            // Update CO and Max marks input names when question name changes
            qInput.addEventListener('input', () => {
              coInput.name = `question_co_map[${partNameInput.value || 'Part_'+i}][${qInput.value}]`;
              maxMarkInput.name = `max_marks[${partNameInput.value || 'Part_'+i}][${qInput.value}]`;
            });

            qDiv.appendChild(qLabel);
            qDiv.appendChild(qInput);
            qDiv.appendChild(coLabel);
            qDiv.appendChild(coInput);
            qDiv.appendChild(maxMarkLabel);
            qDiv.appendChild(maxMarkInput);

            questionsContainer.appendChild(qDiv);
          }
        });

        // Trigger initial question inputs for 1 question by default
        numQInput.dispatchEvent(new Event('change'));

        partDiv.appendChild(partLabel);
        partDiv.appendChild(partNameInput);
        partDiv.appendChild(numQLabel);
        partDiv.appendChild(numQInput);
        partDiv.appendChild(questionsContainer);

        partsContainer.appendChild(partDiv);
      }
    });

  </script>

</body>
</html>
