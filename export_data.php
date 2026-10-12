
<?php
// export_data.php
// Shared data source for all employee exports.

if (!isset($conn)) {
    require_once "config.php";
}

$sql = "
SELECT
    p.*,

    (
        SELECT NULLIF(TRIM(e.degree), '')
        FROM personnel_education e
        WHERE e.personnel_id = p.id
          AND (
              LOWER(e.level) LIKE '%college%'
              OR LOWER(e.level) LIKE '%graduate%'
          )
          AND NULLIF(TRIM(e.degree), '') IS NOT NULL
        ORDER BY e.id DESC
        LIMIT 1
    ) AS education_degree,

    (
        SELECT el.eligibility
        FROM personnel_eligibility el
        WHERE el.personnel_id = p.id
          AND el.eligibility IS NOT NULL
          AND TRIM(el.eligibility) <> ''
        ORDER BY el.id DESC
        LIMIT 1
    ) AS eligibility,

    (
        SELECT GROUP_CONCAT(
            CONCAT(
                'Contract ID: ', COALESCE(c.contract_id, ''),
                '; Position: ', COALESCE(c.position_title, ''),
                '; Employment Type: ', COALESCE(c.employment_type, ''),
                '; Salary Grade: ', COALESCE(c.salary_grade, ''),
                '; Start Date: ', COALESCE(c.start_date, ''),
                '; End Date: ', COALESCE(c.end_date, ''),
                '; Status: ', COALESCE(c.status, '')
            )
            ORDER BY c.start_date DESC, c.id DESC
            SEPARATOR ' || '
        )
        FROM contracts c
        WHERE c.employee_id = p.employee_id
    ) AS contract_history

    FROM personnel p
    ORDER BY CAST(
        SUBSTRING_INDEX(p.employee_id, '-', -1)
        AS UNSIGNED
    ) ASC
";

$result = $conn->query($sql);

if (!$result) {
    die(
        "Export query failed: " .
        htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8')
    );
}

$exportRows = [];

while ($row = $result->fetch_assoc()) {
    $exportRows[] = $row;
}

$exportHeaders = [
    'employee_id' => 'Employee ID',
    'last_name' => 'Last Name',
    'first_name' => 'First Name',
    'middle_name' => 'Middle Name',
    'suffix' => 'Suffix',
    'birth_date' => 'Birth Date',
    'sex' => 'Gender',
    'date_hired' => 'Date Hired',
    'employment_status' => 'Employment Status',
    'office_assignment' => 'Office Assignment',
    'position_title' => 'Position',
    'employment_type' => 'Employment Type',
    'supervisor' => 'Supervisor',
    'daily_rate' => 'Daily Rate',
    'monthly_rate' => 'Monthly Rate',
    'province' => 'Province',
    'place_of_birth' => 'Place of Birth',
    'civil_status' => 'Civil Status',
    'height' => 'Height',
    'weight' => 'Weight',
    'blood_type' => 'Blood Type',
    'umid_no' => 'UMID Number',
    'pagibig_no' => 'PAG-IBIG Number',
    'philhealth_no' => 'PhilHealth Number',
    'psn' => 'PSN',
    'tin_no' => 'TIN',
    'agency_employee_no' => 'Agency Employee Number',
    'citizenship' => 'Citizenship',
    'citizenship' => 'Citizenship',
    'dual_citizenship_type' => 'Dual Citizenship Type',
    'citizenship_country' => 'Citizenship Country',
    'residential_address' => 'Residential Address',
    'telephone_no' => 'Telephone Number',
    'contact_no' => 'Contact Number',
    'email' => 'Email',
    'education_degree' => 'Educational Degree',
    'eligibility' => 'Eligibility',
];