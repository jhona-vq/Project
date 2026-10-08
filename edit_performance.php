<?php

include "auth.php";
include "config.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id <= 0){
    die("Invalid performance evaluation ID.");
}


/* =========================================================
   LOAD PERFORMANCE RECORD
   ========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM performance
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Performance evaluation not found.");
}

$row = $result->fetch_assoc();


/* =========================================================
   UPDATE PERFORMANCE
   ========================================================= */

if(isset($_POST['update'])){

    $employee_name = trim($_POST['employee_name'] ?? '');
    $evaluation_period = trim($_POST['evaluation_period'] ?? '');
    $rating = (float)($_POST['rating'] ?? 0);
    $evaluator = trim($_POST['evaluator'] ?? '');
    $comments = trim($_POST['comments'] ?? '');


    /* =====================================================
       ALLOWED EVALUATION PERIODS
       ===================================================== */

    $allowedPeriods = [

        '2026-Q1',
        '2026-Q2',
        '2026-Q3',
        '2026-Q4',

        '2027-Q1',
        '2027-Q2',
        '2027-Q3',
        '2027-Q4',

        '2028-Q1',
        '2028-Q2',
        '2028-Q3',
        '2028-Q4'

    ];


    /* =====================================================
       VALIDATE EVALUATION PERIOD
       ===================================================== */

    if(!in_array($evaluation_period, $allowedPeriods, true)){

        echo "
        <script>
            alert('Please select a valid Evaluation Period.');
            history.back();
        </script>";

        exit();
    }


    /* =====================================================
       VALIDATE RATING
       ===================================================== */

    if($rating < 1 || $rating > 5){

        echo "
        <script>
            alert('Please enter a valid rating from 1.00 to 5.00.');
            history.back();
        </script>";

        exit();
    }


    /* =====================================================
       GET ADJECTIVAL RATING
       ===================================================== */

    if($rating >= 4.50){

        $status = "Outstanding";

    }
    elseif($rating >= 3.50){

        $status = "Very Satisfactory";

    }
    elseif($rating >= 2.50){

        $status = "Satisfactory";

    }
    elseif($rating >= 1.50){

        $status = "Unsatisfactory";

    }
    else{

        $status = "Poor";

    }


    /* =====================================================
       UPDATE RECORD
       ===================================================== */

    $updateStmt = $conn->prepare("
        UPDATE performance
        SET
            employee_name = ?,
            evaluation_period = ?,
            rating = ?,
            evaluator = ?,
            comments = ?,
            status = ?
        WHERE id = ?
    ");

    $updateStmt->bind_param(
        "ssdsssi",
        $employee_name,
        $evaluation_period,
        $rating,
        $evaluator,
        $comments,
        $status,
        $id
    );


    if(!$updateStmt->execute()){

        die(
            "Failed to update evaluation: " .
            htmlspecialchars($updateStmt->error)
        );

    }


    echo "
    <script>
        alert('Performance evaluation updated successfully.');
        window.location='performance.php';
    </script>";

    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>Edit Performance | JOPMIS</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:800px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
}

.card-header{
    background:#1e3a8a;
    color:white;
    border-radius:15px 15px 0 0 !important;
}

.form-label{
    font-weight:600;
}

</style>

</head>

<body>

<div class="container mt-5 mb-5">

    <div class="card">

        <div class="card-header">

            <h4 class="mb-0">
                <i class="fas fa-edit me-2"></i>
                Edit Performance Evaluation
            </h4>

        </div>


        <div class="card-body p-4">

            <form method="POST">


                <!-- EMPLOYEE NAME -->

                <div class="mb-3">

                    <label class="form-label">
                        Employee Name
                    </label>

                    <input
                        type="text"
                        name="employee_name"
                        class="form-control"
                        value="<?= htmlspecialchars($row['employee_name']); ?>"
                        required>

                </div>


                <!-- EVALUATION PERIOD -->

                <div class="mb-3">

                    <label class="form-label">
                        Evaluation Period
                    </label>

                    <select
                        name="evaluation_period"
                        class="form-select"
                        required>

                        <option value="">
                            -- Select Evaluation Period --
                        </option>


                        <!-- 2026 -->

                        <optgroup label="2026">

                            <option
                                value="2026-Q1"
                                <?= ($row['evaluation_period'] === '2026-Q1') ? 'selected' : ''; ?>>
                                2026 - Q1
                            </option>

                            <option
                                value="2026-Q2"
                                <?= ($row['evaluation_period'] === '2026-Q2') ? 'selected' : ''; ?>>
                                2026 - Q2
                            </option>

                            <option
                                value="2026-Q3"
                                <?= ($row['evaluation_period'] === '2026-Q3') ? 'selected' : ''; ?>>
                                2026 - Q3
                            </option>

                            <option
                                value="2026-Q4"
                                <?= ($row['evaluation_period'] === '2026-Q4') ? 'selected' : ''; ?>>
                                2026 - Q4
                            </option>

                        </optgroup>


                        <!-- 2027 -->

                        <optgroup label="2027">

                            <option
                                value="2027-Q1"
                                <?= ($row['evaluation_period'] === '2027-Q1') ? 'selected' : ''; ?>>
                                2027 - Q1
                            </option>

                            <option
                                value="2027-Q2"
                                <?= ($row['evaluation_period'] === '2027-Q2') ? 'selected' : ''; ?>>
                                2027 - Q2
                            </option>

                            <option
                                value="2027-Q3"
                                <?= ($row['evaluation_period'] === '2027-Q3') ? 'selected' : ''; ?>>
                                2027 - Q3
                            </option>

                            <option
                                value="2027-Q4"
                                <?= ($row['evaluation_period'] === '2027-Q4') ? 'selected' : ''; ?>>
                                2027 - Q4
                            </option>

                        </optgroup>


                        <!-- 2028 -->

                        <optgroup label="2028">

                            <option
                                value="2028-Q1"
                                <?= ($row['evaluation_period'] === '2028-Q1') ? 'selected' : ''; ?>>
                                2028 - Q1
                            </option>

                            <option
                                value="2028-Q2"
                                <?= ($row['evaluation_period'] === '2028-Q2') ? 'selected' : ''; ?>>
                                2028 - Q2
                            </option>

                            <option
                                value="2028-Q3"
                                <?= ($row['evaluation_period'] === '2028-Q3') ? 'selected' : ''; ?>>
                                2028 - Q3
                            </option>

                            <option
                                value="2028-Q4"
                                <?= ($row['evaluation_period'] === '2028-Q4') ? 'selected' : ''; ?>>
                                2028 - Q4
                            </option>

                        </optgroup>

                    </select>

                </div>


                <!-- RATING -->

                <div class="mb-3">

                    <label class="form-label">
                        Numerical Rating
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="1"
                        max="5"
                        name="rating"
                        id="rating"
                        class="form-control"
                        value="<?= htmlspecialchars($row['rating']); ?>"
                        required>

                    <small class="text-muted">
                        Enter a rating from 1.00 to 5.00.
                    </small>

                </div>


                <!-- ADJECTIVAL -->

                <div class="mb-3">

                    <label class="form-label">
                        Adjectival Rating
                    </label>

                    <?php

                    $currentRating = (float)$row['rating'];

                    if($currentRating >= 4.50){

                        $currentAdjectival = "Outstanding";

                    }
                    elseif($currentRating >= 3.50){

                        $currentAdjectival = "Very Satisfactory";

                    }
                    elseif($currentRating >= 2.50){

                        $currentAdjectival = "Satisfactory";

                    }
                    elseif($currentRating >= 1.50){

                        $currentAdjectival = "Unsatisfactory";

                    }
                    else{

                        $currentAdjectival = "Poor";

                    }

                    ?>

                    <input
                        type="text"
                        id="adjectivalRating"
                        class="form-control"
                        value="<?= htmlspecialchars($currentAdjectival); ?>"
                        readonly>

                </div>


                <!-- EVALUATOR -->

                <div class="mb-3">

                    <label class="form-label">
                        Evaluator
                    </label>

                    <input
                        type="text"
                        name="evaluator"
                        class="form-control"
                        value="<?= htmlspecialchars($row['evaluator']); ?>">

                </div>


                <!-- COMMENTS -->

                <div class="mb-3">

                    <label class="form-label">
                        Comments
                    </label>

                    <textarea
                        name="comments"
                        class="form-control"
                        rows="4"><?= htmlspecialchars($row['comments']); ?></textarea>

                </div>


                <!-- BUTTONS -->

                <div class="d-flex justify-content-between">

                    <a
                        href="performance.php"
                        class="btn btn-secondary">

                        <i class="fas fa-arrow-left"></i>
                        Back

                    </a>


                    <button
                        type="submit"
                        name="update"
                        class="btn btn-primary">

                        <i class="fas fa-save"></i>
                        Update Evaluation

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>

const ratingInput =
    document.getElementById("rating");

const adjectivalInput =
    document.getElementById("adjectivalRating");


ratingInput.addEventListener("input", function(){

    const rating =
        parseFloat(this.value);

    let adjectival = "";


    if(!isNaN(rating)){

        if(rating >= 4.50){

            adjectival = "Outstanding";

        }
        else if(rating >= 3.50){

            adjectival = "Very Satisfactory";

        }
        else if(rating >= 2.50){

            adjectival = "Satisfactory";

        }
        else if(rating >= 1.50){

            adjectival = "Unsatisfactory";

        }
        else if(rating >= 1.00){

            adjectival = "Poor";

        }

    }


    adjectivalInput.value =
        adjectival;

});

</script>

</body>

</html>
