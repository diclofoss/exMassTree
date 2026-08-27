<?php

include_once 'stringText.php';

class stringFixed extends stringText {

    public static function getDataType($element) {
        if (isset($element->size)) {
            return "varchar({$element->size})";
        }
        return "varchar(90)";
    }

}
