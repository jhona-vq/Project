<div class="card shadow-sm">

<div class="card-header d-flex justify-content-between align-items-center">

    <h5 class="mb-0">
        <i class="fas fa-briefcase text-primary me-2"></i>
        Service Record
    </h5>

    <div class="d-flex gap-2">

        <input type="text"
               id="searchService"
               class="form-control form-control-sm"
               placeholder="Search..."
               style="width:200px;">

        <button class="btn btn-success btn-sm"
                onclick="window.print()">
            <i class="fas fa-download"></i> Download
        </button>

    </div>

</div>

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle mb-0"
id="serviceTable">

<thead class="table-light">

<tr>

<th style="width:220px;">Term Cover</th>

<th>Position</th>

<th style="width:180px;">Employment Type</th>

<th style="width:180px;">Monthly Salary</th>

<th style="width:120px;">Status</th>

</tr>

</thead>

<tbody>

<?php

$getService = $conn->query("
SELECT *
FROM service_records
WHERE personnel_id='$id'
ORDER BY date_from DESC
");

if($getService->num_rows > 0){

while($sr = $getService->fetch_assoc()){

$status = strtolower($sr['status']);

$badge = "secondary";

if($status=="active"){
    $badge="success";
}
elseif($status=="terminated"){
    $badge="dark";
}
elseif($status=="renewed"){
    $badge="primary";
}

?>

<tr>

<td>

<strong>Effective:</strong><br>

From:
<?= date('M d, Y',strtotime($sr['date_from'])) ?>

<br>

Until:
<?= date('M d, Y',strtotime($sr['date_to'])) ?>

</td>

<td>

<strong><?= $sr['position_title']; ?></strong>

<br>

<small class="text-muted">

Office:
<?= $sr['office_assignment']; ?>

</small>

</td>

<td>

Job Order

</td>

<td>

₱<?= number_format($sr['monthly_rate'],2); ?>

</td>

<td>

<span class="badge bg-<?= $badge; ?>">

<?= strtoupper($sr['status']); ?>

</span>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="5" class="text-center">

No Service Record Found.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

