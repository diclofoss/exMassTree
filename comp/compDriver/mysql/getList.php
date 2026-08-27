<?php

$WHERE = "";
if ($id) {
    $WHERE = "WHERE " . mysql::$parentIdName . " = " . $id;
}

//array("datetime" => array(array(">=", "<="), "AND", $dateFrom, $dateTo))

if ($fields) {
    foreach ($fields as $var => $val) {
        if ($WHERE) {
            $WHERE .= " AND ";
        }
        if (is_array($val)) {
            $WHERE .= " ( ";
            for ($i = 2; $i < count($val); $i++) {
                if ($WHERE && $i != 2) {
                    $WHERE .= " " . $val[1] . " ";
                }
                if (is_array($val[0])) {
                    $WHERE .= " $var " . $val[0][$i - 2] . " '" . $val[$i] . "'";
                } else {
                    $WHERE .= " $var " . $val[0] . " '" . $val[$i] . "'";
                }
            }
            $WHERE .= " ) ";
        } else {
            $WHERE .= " $var = '$val'";
        }
    }
}

if ($fieldsExclude) {
    foreach ($fieldsExclude as $var => $val) {
        if (is_array($val)) {
            foreach ($val as $v) {
                if ($WHERE) {
                    $WHERE .= " AND ";
                }
                $WHERE .= " $var <> '$v'";
            }
        } else {
            if ($WHERE) {
                $WHERE .= " AND ";
            }
            $WHERE .= " $var <> '$val'";
        }
    }
}

if ($WHERE && !$id) {
    $WHERE = " WHERE $WHERE ";
}

$ORDER_BY = "";
if ($orderByList) {
    foreach ($orderByList as $orderBy) {
        if ($ORDER_BY) {
            $ORDER_BY .= ", ";
        }

        $ORDER_BY .= $orderBy;
    }
}

if ($ORDER_BY) {
    $ORDER_BY = " ORDER BY $ORDER_BY ";
}

$LIMIT = "";
if ($limit) {
    $LIMIT = "LIMIT $limit";
}

try {
    $dbQuery = mysql::$dbConnect->query("SELECT * FROM " . $this->tblname . " $WHERE $ORDER_BY $LIMIT");
} catch (PDOException $ex) {
    return;
}

if (!$dbQuery) {
    return;
}
while ($compObj = $dbQuery->fetchObject()) {
    $cubCompObjArray = array();
    foreach ($withList as $with) {
        $TElement = $this->compDriver($with);
        $cubCompObjArray = $TElement->getList($compObj->n_id);
        $paramName = $with . "List";
        $compObj->$paramName = $cubCompObjArray;
    }
    foreach ($withObjects as $field_id => $objectName) {
        $TElement = $this->compDriver($objectName);
        $fieldName = preg_replace("/_id/", "", $field_id);
        $compObj->$fieldName = $TElement->get($compObj->$field_id);
    }

    $dataList[] = $compObj;
}
