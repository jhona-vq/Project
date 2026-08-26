<?php
include "config.php";

$current = $conn->query("
SELECT theme
FROM system_settings
WHERE id=1
")->fetch_assoc();

$newTheme = ($current['theme']=='light')
? 'dark'
: 'light';

$conn->query("
UPDATE system_settings
SET theme='$newTheme'
WHERE id=1
");

header("Location: ".$_SERVER['HTTP_REFERER']);
exit();
?>