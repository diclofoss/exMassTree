<?php

include_once 'stringInt.php';

class stringFloat extends stringInt {

    public static function getDataType($element) {
        return "float";
    }
}
