<?php

class mysql implements compDriver {

    static $dbConnect;
    static $tablePrefix;
    static $idName;
    static $parentIdName;
    var $objName;
    var $tblname;

    function getIdName() {
        return self::$idName;
    }

    function getParentIdName() {
        return self::$parentIdName;
    }

    function getParentId($data) {
        if (isset($data->{$this::$parentIdName})) {
            return $data->{$this::$parentIdName};
        }
        return "";
    }

    static public function connectDb($config) {
        include_once 'mysql/connectDb.php';
    }

    static public function compDriver($objName) {
        return new mysql($objName);
    }

    function __construct($objName) {
        $this->objName = $objName;
        $this->tblname = mysql::$tablePrefix . $objName;
    }

    public function copyDocument($file) {
        
    }

    public function count($id = "", $fields = array(), $fieldsExclude = array()) {
        
    }

    public function countBySql($sql) {
        
    }

    public function createByFields($fields) {
        include 'mysql/createByFields.php';
        return $id;
    }

    public function createByPost($fields = array(), $requiredFields = array()) {
        include 'mysql/createByPost.php';
        return $id;
    }

    public function createEmpty() {
        
    }

    public function delete($id, $fields = array()) {
        $code = 1;
        include 'mysql/delete.php';
        return $code;
    }

    public function get($id, $fields = array(), $withList = array(), $withObjects = array()) {
        include 'mysql/get.php';
        return $compObj;
    }

    public function getByFields($fields, $withList = array(), $withObjects = array()) {
        include 'mysql/getByFields.php';
        return $compObj;
    }

    public function getBySql($sql, $withList = array(), $withObjects = array()) {
        include 'mysql/getBySql.php';
        return $row;
    }

    public function getEmpty() {
        include 'mysql/getEmpty.php';
    }

    public function getList($id = "", $fields = array(), $withList = array(), $fieldsExclude = array(), $withObjects = array(), $orderByList = array(), $limit = "") {
        $dataList = array();
        include 'mysql/getList.php';
        return $dataList;
    }

    public function getListBySql($sql, $withList = array(), $withObjects = array()) {
        include 'mysql/getListBySql.php';
        return $compObjArray;
    }

    public function thumbnailImage($file, $width, $height) {
        
    }

    public function updateByPost($id, $fields = array(), $withFields = array(), $fieldsExclude = array()) {
        $code = 1;
        include 'mysql/updateByPost.php';
        return $code;
    }

    public function createBySql($sql) {
        include 'mysql/createBySql.php';
        return $id;
    }

    public function updateBySql($sql) {
        include 'mysql/updateBySql.php';
    }

    public function updateFields($id, $fields) {
        $code = 1;
        include 'mysql/updateFields.php';
        return $code;
    }

    public function uploadDocument($file) {
        include 'mysql/uploadDocument.php';
        return $path;
    }

    function translitIt($str) {
        $tr = array(
            "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
            "Д" => "D", "Е" => "E", "Ж" => "J", "З" => "Z", "И" => "I",
            "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
            "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
            "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
            "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya"
        );
        return strtr($str, $tr);
    }

}
