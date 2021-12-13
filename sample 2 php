<?php

function reg_number($id)
        {
            $regNum = '';
            $uniqueId = str_pad($id, 4, '0', STR_PAD_LEFT);
            $date = date('y');
            $regNum = "SCH" . '\\' . $date . '\\' . $uniqueId;
            return $regNum;
        }; 

echo reg_number(5);

?>
