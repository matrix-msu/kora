<?php

if (($handle = fopen("test.csv", "r")) !== FALSE) {
    $row = 0;
    $result = $fields = array();
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        $num = count($data);
        for ($c=0; $c < $num; $c++) {
            if ($row == 0) {
                $result[$c] = [];
                array_push($fields, $data[$c]);
            } else {
                if ($data[$c])
                    array_push($result[$c], $data[$c]);
                // $result[$c] = $data[$c];
            }
            // echo $data[$c] . "\n";
        }
        $row++;
    }
    fclose($handle);
    $data = array();
    for ($i=0; $i < count($result); $i++) {
        $data[$fields[$i]] = $result[$i];
    }
    echo json_encode($data);
}

?>
