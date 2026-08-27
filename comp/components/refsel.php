<?php

class refsel implements item {

    public static function render($config, $component, $element, $data, &$jsInclude) {
        ob_start();
        $TElement = $config->compDriver("element");
        $valueText = $element->default;
        $rawVal = self::rowField($data, $element->name);
        if ($rawVal) {
            $valueText = "";
            $sql = str_replace("#VALUE#", $rawVal, $element->select);
            $sql = self::applyRowPlaceholders($sql, $data);
            $row = $TElement->getBySql($sql);
            if ($row) {
                foreach ($row as $var => $val) {
                    if ($val == "element" || $val == "t_element") {
                        continue;
                    }
                    $valueText .= $val . " ";
                }
            }
            if (trim($valueText) === '') {
                $valueText = (string) $rawVal;
            }
        }
        include("templates/components/refsel.php");
        return ob_get_clean();
    }

    public static function isTable() {
        return false;
    }

    public static function getDataType($element) {
        return "int(11)";
    }

    public static function isNull($element) {
        if (isset($element->notNull) && $element->notNull) {
            return false;
        }
        return true;
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/stringFrontFilter.php");
        return ob_get_clean();
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        $TElement = $config->compDriver("element");
        $valueText = "<i>" . $element->default . "</i>";
        $rawVal = self::rowField($data, $element->name);
        if ($rawVal) {
            $valueText = "";
            $sql = str_replace("#VALUE#", $rawVal, $element->select);
            $sql = self::applyRowPlaceholders($sql, $data);
            $row = $TElement->getBySql($sql);
            if ($row) {
                foreach ($row as $var => $val) {
                    if ($val == "element" || $val == "t_element") {
                        continue;
                    }
                    $valueText .= $val . " ";
                }
            }
            if (trim(strip_tags($valueText)) === '') {
                $valueText = (string) $rawVal;
            }
        }

        return $valueText;
    }

    /** Replace #ROW|field# with values from the current record (sibling columns). */
    private static function applyRowPlaceholders($sql, $data) {
        if (!preg_match_all("/#ROW\\|([^#]+)#/", $sql, $matches)) {
            return $sql;
        }
        static $npcTables = array(
            'farm', 'grapesfarm', 'newplant', 'mine', 'labm', 'police', 'meria',
            'vehschool', 'radio', 'blackmarket', 'firmorg_zavkhoz', 'firmorg_retail',
        );
        foreach ($matches[1] as $field) {
            $val = self::rowField($data, $field);
            if ($field === 'npc_type') {
                $val = strtolower(trim((string) $val));
                if ($val === '' || !in_array($val, $npcTables, true)) {
                    $val = 'farm';
                }
            }
            $sql = str_replace("#ROW|" . $field . "#", (string) $val, $sql);
        }
        return $sql;
    }

    private static function rowField($data, $field) {
        if (is_object($data) && isset($data->{$field})) {
            return $data->{$field};
        }
        if (is_array($data) && isset($data[$field])) {
            return $data[$field];
        }
        return null;
    }

    public static function getJs() {
        return "js/components/refsel.js";
    }

    public static function refselData($application, $element) {
        $tblPrefix = $application->config->database->prefix;
        $id = $_GET['id'];
        $parentId = "";
        if (isset($_GET['parentId'])) {
            $parentId = $_GET['parentId'];
        }
        $TElement = $application->compDriver("element");
        if (!isset($_GET['q'])) {
            $_GET['q'] = "";
        }
        if (!$parentId) {
            $parentId = $_GET['id'];
        }
        $sql = str_replace("#KEYWORD#", $_GET['q'], $element->find);
        $sql = str_replace("#ID#", $parentId, $sql);
        if (preg_match_all("/#REFSEL\\|([^#]+)#/", $element->find, $matches)) {
            static $npcTables = array(
                'farm', 'grapesfarm', 'newplant', 'mine', 'labm', 'police', 'meria',
                'vehschool', 'radio', 'blackmarket', 'firmorg_zavkhoz', 'firmorg_retail',
            );
            foreach ($matches[1] as $param) {
                if (!isset($_GET[$param])) {
                    continue;
                }
                $val = (string) $_GET[$param];
                if ($param === 'npc_type') {
                    $val = strtolower($val);
                    if (!in_array($val, $npcTables, true)) {
                        $val = 'farm';
                    }
                }
                $sql = str_replace("#REFSEL|" . $param . "#", $val, $sql);
            }
        }
        $sql .= " LIMIT 40";
        $rows = $TElement->getListBySql($sql);
        $valueText = array();
        if (refsel::isNull($element)) {
            $array = array();
            $array["id"] = "NULL";
            $array["text"] = "Нет значения";
            $valueText[] = $array;
        }
        foreach ($rows as $row) {
            $i = 0;
            $array = array();
            foreach ($row as $var => $val) {
                if ($val == "element" || $val == $tblPrefix."element") {
                    continue;
                }
                $i++;
                if ($i == 1) {
                    $array["id"] = $val;
                }
                if ($i == 2) {
                    $array["text"] = $val;
                    $valueText[] = $array;
                }
            }
        }
        print json_encode(array("results" => $valueText, "sql" => $sql));
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    static function prepareFilter($component, $parentElement, $element, $value) {
        $sql = str_replace("#KEYWORD#", $value, $element->find);
#        $sql = preg_replace("/(SELECT [^,]+)[^FROM]*/", "\\1 ", $sql);
        $sql = preg_replace("/(SELECT [^,]+)((?!FROM).)+/", "\\1 ", $sql);
//        $sql = preg_replace("/(SELECT [^,]+).*(?=FROM)/", "\\1 ", $sql);
	if (!preg_match('/\./', $element->name)) {
        	return "`" . $element->name . "` IN ($sql)";
	} else {
        	return "" . $element->name . " IN ($sql)";
	}
    }

    public static function prepareDataForSave($component, $element, $value) {
        if ($value) {
            return $value;
        } else {
            return "NULL";
        }
    }

    public static function prepareDataAfterSave($component, $element, $value) {
        return $value;
    }

    public static function hasSingleLine() {
        
    }

    public static function getNoPostValue() {
        return "";
    }

    public static function getCss() {
        return "";
    }

}
