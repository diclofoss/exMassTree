<?php

include_once 'compDriver/mysql.php';

interface compDriver {

    static function connectDb($config);

    static function compDriver($objName);

    function getIdName();

    function getParentIdName();

    function createByPost($fields = array(), $requiredFields = array());

    function createByFields($fields);

    function uploadDocument($file);

    function copyDocument($file);

    function thumbnailImage($file, $width, $height);

    function getEmpty();

    function get($id, $fields = array(), $withList = array(), $withObjects = array());

    function getByFields($fields, $withList = array(), $withObjects = array());

    function getList($id = "", $fields = array(), $withList = array(), $fieldsExclude = array(), $withObjects = array(), $orderByList = array(), $limit = "");

    function count($id = "", $fields = array(), $fieldsExclude = array());

    function countBySql($sql);

    function getListBySql($sql, $withList = array(), $withObjects = array());

    function getBySql($sql, $withList = array(), $withObjects = array());

    function createEmpty();

    function updateFields($id, $fields);

    function updateByPost($id, $fields = array(), $withFields = array(), $fieldsExclude = array());

    function updateBySql($sql);

    function delete($id, $fields = array());
}
