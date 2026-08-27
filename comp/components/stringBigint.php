<?php

include_once 'stringFloat.php';

class stringBigint extends stringFloat {

    public static function getDataType($element) {
        return "bigint(20)";
    }

}
