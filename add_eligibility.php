<?php
include "auth.php";
include "config.php";
include "role_access.php";

allowRoles([
           'System Administrator',
           'HR Administrator'
]);

$personnel_id = intval(
    $_GET['personnel_id']
    ?? $_GET['id']
    ?? $_POST['personnel_id']
    ?? 0
);

if($personnel_id <= 0) {
    die("Invalid personnel ID.");
}

$stmtPersonnel = $conn->prepare("
    SELECT id, employee_id, first_name, middle_name, last_name
    FROM personnel
    WHERE id = ?
    LIMIT 1
");

$stmtPersonnel->bind_param(
    "i",
    $personnel_id
);

$stmtPersonnel->execute();

$stmtPersonnelResult = $stmtPersonnel->get_result();

if($personnelResult->num_rows ===0){
    die("Personnel record not found.");
}

$personnel = $personnelResult-.fetch-assoc();

$stmtPersonnel->close();

if(isset($_POST['save_eligibility'])){

    $eligibility    = trim($_POST['eligibility']);
    $rating         = trim($_POST['rating']);
    $exam_date      = $_POST['exam_date'];
    $exam_place     = trim($_POST['exam_place']);
    $license_number = trim($_POST['license_number']);
    $valid_until    = $_POST['valid_until'];

if(!is-array($eligibilities)) {
    $eligibilities = [];
}

$saveCount = 0;

$conn->begin_transaction();
    try {
        foreach( $eligibilities as $index => $eligibility) {
            $eligibility = trim($eligibility);

            $rating = trim(
                $ratings[$index] ?? ''
            );
            $exam_date = trim(
                $exam-dates[$index] ?? ''
            );
            $exam_place = trim(
                $exam_places[$index] ? ''
            );
            $license_number = trim(
                $licenses[$index] ?? ''
            );
            $valid_until = trim(
                $valid_untils[$index] ?? ''
            );

            if ($eligibilty === '') {
                continue;
            }

            $exam_date = ($exam_date !== '')
                ? $exam_date
                ; null;
    

    $stmt = $conn->prepare("
    INSERT INTO personnel_eligibility
    (
        personnel_id,
        eligibility,
        rating,
        exam_date,
        exam_place,
        license_number,
        valid_until
    )
    VALUES(?,?,?,?,?,?,?)
    ");

    if (!$stmt) {
        throw new Exception(
            "Prepare failed: " . $conn->error
        );
    }

    $stmt->bind_param(
        "issssss",
        $personnel_id,
        $eligibility,
        $rating,
        $exam_date,
        $exam_place,
        $license_number,
        $valid_until
    );

    if($stmt->execute()){

        throw new Exception(
            "Unable to save eligibilty: " .
            $stmt->error
        );
    }

    $stmt->close();

    $saveCount++;
}

if ($saveCount === 0) {
    $conn->rollback();

    echo "
    <script>
        alert ('Please enter at least one eligibility.');
        history.back();
    </script>
    ";

    exit;
}

$conn->commit();

echo "
<script>

    alert(
        '" . $savedCount . " eligibility record(s) saved successfully.'
    );

    window.location.href =
        'personnel.php?id= . $personnel-id . "';
</script>
";

exit;

} catch (Exception $e) {
    $conn->rollback();

    die(
        "Error saving eligibility; " .
        htmlspeechialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
        content="width=device-width, initial-scale=1.0">

<title>Add Civil Service Eligibility</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">


<script>
if(localStorage.getItem("theme") === "dark"){
    document.documentElement.classList.add("dark-mode");
}
</script>

<style>

*{
    box-sizing:border-box;
}

body{
    background:#f5f7fb;
    color:#0f172a;
}

.container{
    max-width:1100px;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    overflow:hidden;
}

.card-header{
    font-size:20px;
    font-weight:bold;
    padding:18px 22px;
}

.personel-info{
    background:#f8fafc;
    borde-bottom:1px solod #e5e7eb;
    padding: 18px 22px;
}
.personel-name{
    font-size:20px;
    font-weight:700;
}
.personnel-id{
    color:#64748b;
    font-size:14px;
}
.eligibility-card[
    border:1px solid #e5e7eb;
    border-radius:15px;
    margin-bottom:20px;
    overflow:hidden;
    background:#fff;
}
.eligibility-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#eff6ff;
    padding:12px 16px;
    border-bottom:1px solid #dbefe;
}
.eligibility-title{
    font=-weight:700;
    color:#2563eb;
}
.eligibility-body{
    padding:20px;
}
label{
    font-weight:600;
    margin-bottom:6px;
}
.form-control,
.form-select{
    min-height:44px;
    border-radius:9px;
}
.remove-btn{
    border-radius:8px;
}
.action-buttons{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-top:25px;
    padding-top:20px;
    border-top:1px solid #e5e7eb;
}
.dark-mode body{
    backround:#0f172a;
    color:fff;
}
.dark-mode .card{
    background:#1e293b;
    color:#fff;
}
.dark-mode .card-header{
    background:#1e293b !important;
    color:#fff;
}
.dark-mode .personnel-info{
    backgound:#172033;
    border-color:#334155;
}
.dark-mode .personnel-id{
    color:#cbd5e1;
}
.dark-mode .eligibility-card{
    background:#1e293b;
    border-color:#475569;
}
.dark-mode .eligibility-header{
    background:#172554;
    border-color:#334155;
}
.dark-mode .eligibility-title{
    color:#93c5fd;
}
.dark-mode label{
    color:#fff;
}
.dark-mode .form-control,
.dark-mode .form-select{
    background:#334155;
    color:#fff;
    border-color:#475569;
}
.dark-mode .form-control::placeholder{
    color:#cbd5e1;
}

@media(max-width:768px){

    .contsiner{
        padding:10px;
    }
    .card-header{
        font-size:18px;
    }
    personnel-info{
        padding:15px;
    }
    .eligibility-body{
        padding:15px;
    }
    .action-buttons[
        flex-direction:column;
        align-items:stretch;
    }
    .action-buttons .btn{
        width:100%;
    }
}

</style>

</head>

<body>

<div class="container py-4">

<div class="card">

<div class="card-header bg-primary text-white">

<i class="fas fa-award me-2"></i>

Civil Service Eligibility

</div>

<div class="personnel-info">
    <div class="personnel-name">
        <?= htmlspecialchars(
            trim(
                ($personnel['first_name'] ?? '') . '' .
                ($personnel['middle_name'] ?? '') . '' .
                ($personnel['last_name'] ?? '') 
            ),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </div>

    <dic class="personnel_id">
        Emplyee ID:

        <strong>
            <?= htmlspecialchars(
                $personnel['employee-id'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </strong>
    </div>
</div>

<div class="card-body p-4">

<form method="POST">


<!-- =====================================================
     IMPORTANT PERSONNEL ID
===================================================== -->

<input
type="hidden"
name="personnel_id"
value="<?= $personnel_id ?>">


<!-- =====================================================
     ELIGIBILITY CONTAINER
===================================================== -->

<div id="eligibilityContainer">


    <!-- =================================================
         FIRST ELIGIBILITY
    ================================================== -->

    <div class="eligibility-card">

        <div class="eligibility-header">

            <div class="eligibility-title">

                <i class="fas fa-award me-2"></i>

                Eligibility #1

            </div>

        </div>


        <div class="eligibility-body">


            <div class="row">


                <!-- ELIGIBILITY -->

                <div class="col-md-12 mb-3">

                    <label>
                        Civil Service Eligibility
                    </label>

                    <input
                    type="text"
                    name="eligibility[]"
                    class="form-control"
                    placeholder="Example: Career Service Professional"
                    required>

                </div>


                <!-- RATING -->

                <div class="col-md-4 mb-3">

                    <label>
                        Rating (If Applicable)
                    </label>

                    <input
                    type="text"
                    name="rating[]"
                    class="form-control"
                    placeholder="Example: 84.25">

                </div>


                <!-- EXAM DATE -->

                <div class="col-md-4 mb-3">

                    <label>
                        Date of Examination / Conferment
                    </label>

                    <input
                    type="date"
                    name="exam_date[]"
                    class="form-control">

                </div>


                <!-- VALID UNTIL -->

                <div class="col-md-4 mb-3">

                    <label>
                        Valid Until
                    </label>


                    <select
                    name="validity_type[]"
                    class="form-select validity-select"
                    onchange="handleValidUntil(this)"
                    >

                        <option value="">
                            Select Validity
                        </option>

                        <option value="No Expiration">
                            No Expiration
                        </option>

                        <option value="As Applicable">
                            As Applicable
                        </option>

                        <option value="date">
                            Specific Date
                        </option>

                    </select>


                    <div
                    class="valid-date-container mt-2"
                    style="display:none;"
                    >

                        <input
                        type="date"
                        class="form-control valid-date-input"
                        onchange="updateValidUntil(this)"
                        >

                    </div>


                    <input
                    type="hidden"
                    name="valid_until[]"
                    class="valid-until-hidden"
                    >

                </div>


                <!-- EXAM PLACE -->

                <div class="col-md-6 mb-3">

                    <label>
                        Place of Examination / Conferment
                    </label>

                    <input
                    type="text"
                    name="exam_place[]"
                    class="form-control"
                    placeholder="Example: Baguio City">

                </div>


                <!-- LICENSE -->

                <div class="col-md-6 mb-3">

                    <label>
                        License Number (If Applicable)
                    </label>

                    <input
                    type="text"
                    name="license_number[]"
                    class="form-control"
                    placeholder="If applicable">

                </div>


            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     ADD ANOTHER
===================================================== -->

<div class="mb-4">

    <button
    type="button"
    class="btn btn-outline-primary"
    onclick="addEligibility()">

        <i class="fas fa-plus me-1"></i>

        Add Another Eligibility

    </button>

</div>


<!-- =====================================================
     BUTTONS
===================================================== -->

<div class="action-buttons">


    <a
    href="personnel.php?id=<?= $personnel_id ?>"
    class="btn btn-secondary">

        <i class="fas fa-arrow-left me-1"></i>

        Back

    </a>


    <button
    type="submit"
    name="save_eligibility"
    class="btn btn-primary">

        <i class="fas fa-save me-1"></i>

        Save All Eligibility

    </button>


</div>


</form>


</div>

</div>

</div>


<script>

/* =========================================================
   DARK MODE
========================================================= */

document.addEventListener("DOMContentLoaded", function(){

    if(localStorage.getItem("theme") === "dark"){

        document.documentElement.classList.add("dark-mode");

        document.body.classList.add("dark-mode");

    }

});


/* =========================================================
   ELIGIBILITY COUNTER
========================================================= */

let eligibilityCount = 1;


/* =========================================================
   ADD ANOTHER ELIGIBILITY
========================================================= */

function addEligibility(){

    eligibilityCount++;

    const container =
        document.getElementById(
            "eligibilityContainer"
        );


    const card =
        document.createElement("div");

    card.className =
        "eligibility-card";


    card.innerHTML = `

        <div class="eligibility-header">

            <div class="eligibility-title">

                <i class="fas fa-award me-2"></i>

                Eligibility #${eligibilityCount}

            </div>


            <button
            type="button"
            class="btn btn-sm btn-danger remove-btn"
            onclick="removeEligibility(this)"
            >

                <i class="fas fa-trash"></i>

                Remove

            </button>

        </div>


        <div class="eligibility-body">

            <div class="row">


                <div class="col-md-12 mb-3">

                    <label>
                        Civil Service Eligibility
                    </label>

                    <input
                    type="text"
                    name="eligibility[]"
                    class="form-control"
                    placeholder="Example: Career Service Professional"
                    required>

                </div>


                <div class="col-md-4 mb-3">

                    <label>
                        Rating (If Applicable)
                    </label>

                    <input
                    type="text"
                    name="rating[]"
                    class="form-control"
                    placeholder="Example: 84.25">

                </div>


                <div class="col-md-4 mb-3">

                    <label>
                        Date of Examination / Conferment
                    </label>

                    <input
                    type="date"
                    name="exam_date[]"
                    class="form-control">

                </div>


                <div class="col-md-4 mb-3">

                    <label>
                        Valid Until
                    </label>

                    <select
                    name="validity_type[]"
                    class="form-select validity-select"
                    onchange="handleValidUntil(this)"
                    >

                        <option value="">
                            Select Validity
                        </option>

                        <option value="No Expiration">
                            No Expiration
                        </option>

                        <option value="As Applicable">
                            As Applicable
                        </option>

                        <option value="date">
                            Specific Date
                        </option>

                    </select>


                    <div
                    class="valid-date-container mt-2"
                    style="display:none;"
                    >

                        <input
                        type="date"
                        class="form-control valid-date-input"
                        onchange="updateValidUntil(this)"
                        >

                    </div>


                    <input
                    type="hidden"
                    name="valid_until[]"
                    class="valid-until-hidden"
                    >

                </div>


                <div class="col-md-6 mb-3">

                    <label>
                        Place of Examination / Conferment
                    </label>

                    <input
                    type="text"
                    name="exam_place[]"
                    class="form-control"
                    placeholder="Example: Baguio City">

                </div>


                <div class="col-md-6 mb-3">

                    <label>
                        License Number (If Applicable)
                    </label>

                    <input
                    type="text"
                    name="license_number[]"
                    class="form-control"
                    placeholder="If applicable">

                </div>


            </div>

        </div>
    `;


    container.appendChild(card);
}


/* =========================================================
   REMOVE ELIGIBILITY
========================================================= */

function removeEligibility(button){

    const card =
        button.closest(".eligibility-card");


    if(card){

        card.remove();

        renumberEligibilities();

    }

}


/* =========================================================
   RENUMBER CARDS
========================================================= */

function renumberEligibilities(){

    const cards =
        document.querySelectorAll(
            ".eligibility-card"
        );


    cards.forEach(function(card, index){

        const title =
            card.querySelector(
                ".eligibility-title"
            );


        if(title){

            title.innerHTML =
                `<i class="fas fa-award me-2"></i>
                 Eligibility #${index + 1}`;

        }

    });


    eligibilityCount = cards.length;
}


/* =========================================================
   VALID UNTIL
========================================================= */

function handleValidUntil(select){

    const card =
        select.closest(
            ".eligibility-card"
        );


    const dateContainer =
        card.querySelector(
            ".valid-date-container"
        );


    const dateInput =
        card.querySelector(
            ".valid-date-input"
        );


    const hiddenInput =
        card.querySelector(
            ".valid-until-hidden"
        );


    if(select.value === "No Expiration"){

        dateContainer.style.display = "none";

        dateInput.required = false;

        dateInput.value = "";

        hiddenInput.value =
            "No Expiration";

    }


    else if(select.value === "As Applicable"){

        dateContainer.style.display = "none";

        dateInput.required = false;

        dateInput.value = "";

        hiddenInput.value =
            "As Applicable";

    }


    else if(select.value === "date"){

        dateContainer.style.display =
            "block";

        dateInput.required = true;

        hiddenInput.value =
            dateInput.value;

    }


    else{

        dateContainer.style.display =
            "none";

        dateInput.required = false;

        dateInput.value = "";

        hiddenInput.value = "";

    }

}


/* =========================================================
   UPDATE DATE
========================================================= */

function updateValidUntil(dateInput){

    const card =
        dateInput.closest(
            ".eligibility-card"
        );


    const select =
        card.querySelector(
            ".validity-select"
        );


    const hiddenInput =
        card.querySelector(
            ".valid-until-hidden"
        );


    if(select.value === "date"){

        hiddenInput.value =
            dateInput.value;

    }

}

</script>


</body>

</html>
