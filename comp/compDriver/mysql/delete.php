<?php

$idName = mysql::$idName;

$WHERE = "";
if ($id) {
    if (is_array($id)) {
        foreach ($id as $field => $value) {
            if ($WHERE) {
                $WHERE .= " AND ";
            }
            $WHERE = "$field = '$value'";
        }
    } else {
        $WHERE = "$idName = $id";
    }
}

if ($fields) {
    foreach ($fields as $var => $val) {
        if ($WHERE) {
            $WHERE .= " AND ";
        }
        $WHERE .= " $var = '$val'";
    }
}

$pdo = mysql::$dbConnect;

// aiquest: перед удалением ребра снять parent_id с нод (иначе FK 1451)
if ($this->tblname === 't_aiquest_node_edge' && $id && !is_array($id)) {
    try {
        $pdo->prepare('UPDATE `t_aiquest_node` SET `parent_id` = NULL WHERE `parent_id` = ?')
            ->execute(array((int) $id));
    } catch (Exception $e) {
        // ignore
    }
}

// aiquest: прогресс игроков держит FK на квест
if ($this->tblname === 't_aiquest' && $id && !is_array($id)) {
    try {
        $pdo->prepare('UPDATE `t_aiquest_node` SET `parent_id` = NULL WHERE `item_id` = ?')
            ->execute(array((int) $id));
    } catch (Exception $e) {
        // ignore
    }
    try {
        $pdo->prepare('DELETE FROM `t_siteuser_aiquest` WHERE `aiquest_id` = ?')
            ->execute(array((int) $id));
    } catch (Exception $e) {
        // ignore
    }
}

try {
    $dbQuery = $pdo->prepare("DELETE FROM " . $this->tblname . " WHERE $WHERE");
    $dbQuery->execute();
} catch (PDOException $ex) {
    print $ex->getMessage();
    return $code = 1;
}

if (!$dbQuery) {
    $errorInfo = $pdo->errorInfo();
    if (!empty($errorInfo[2])) {
        print $errorInfo[2];
    }
    return $code = 1;
}

return $code = 0;
