<?php
include "config.php";

$tables = array();

$result = mysqli_query($conn,"SHOW TABLES");

while($row = mysqli_fetch_row($result)){
    $tables[] = $row[0];
}

$return = '';

foreach($tables as $table){

    $result = mysqli_query($conn,"SELECT * FROM $table");

    $num_fields = mysqli_num_fields($result);

    $return .= "DROP TABLE IF EXISTS $table;";
    $row2 = mysqli_fetch_row(
        mysqli_query($conn,"SHOW CREATE TABLE $table")
    );

    $return .= "\n\n".$row2[1].";\n\n";

    while($row = mysqli_fetch_row($result)){

        $return .= "INSERT INTO $table VALUES(";

        for($j=0;$j<$num_fields;$j++){

            $row[$j] = addslashes($row[$j]);

            if(isset($row[$j])){
                $return .= '"'.$row[$j].'"';
            }else{
                $return .= '""';
            }

            if($j < ($num_fields-1)){
                $return .= ',';
            }
        }

        $return .= ");\n";
    }

    $return .= "\n\n";
}

$filename =
"JOPMIS_Backup_".date("Y-m-d_H-i-s").".sql";

header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=$filename");

echo $return;
exit();
?>