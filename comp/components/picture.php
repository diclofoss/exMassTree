<?php

class picture implements item {

    public static function getDataType($element) {
        return "varchar(90)";
    }

    public static function getJs() {
        return "js/components/picture.js";
    }

    public static function isNull($application) {
        return false;
    }

    public static function isTable() {
        return false;
    }

    public static function prepareDataForSave($component, $element, $value) {
// Если отправлена команда на ресайз
        if ($element->dependedPictures && $value) {
            foreach ($element->dependedPictures as $dependedPicture) {
                if (isset($_POST["picture_doResize_" . $dependedPicture]) && $_POST["picture_doResize_" . $dependedPicture] == "true") {
                    return $value;
                }
            }
        } else if ($element->dependPicture && $value) {
            if (isset($_POST["picture_doResize_" . $element->name]) && $_POST["picture_doResize_" . $element->name] == "true") {
                return $value;
            }
        }
        if (isset($_POST["picture_delete_" . $element->name]) && $_POST["picture_delete_" . $element->name] == 'true') {
// Удалить картинку если идет команда на удаление
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $value))
                unlink($_SERVER['DOCUMENT_ROOT'] . $value);
            return $value = "";
        } else if ($element->dependPicture && isset($_FILES["picture_upload_" . $element->dependPicture]['tmp_name']) && $_FILES["picture_upload_" . $element->dependPicture]['tmp_name']) {
// Если идет загрузка фотографий сгенерировать имя заного и удалить старую фотографию как для случая со своей так и с зависимой фотографией
            $postfix = "";
            if (exif_imagetype($_FILES["picture_upload_" . $element->dependPicture]['tmp_name']) == IMAGETYPE_JPEG) {
                $postfix = ".jpg";
            } else if (exif_imagetype($_FILES["picture_upload_" . $element->dependPicture]['tmp_name']) == IMAGETYPE_GIF) {
                $postfix = ".gif";
            } else if (exif_imagetype($_FILES["picture_upload_" . $element->dependPicture]['tmp_name']) == IMAGETYPE_PNG) {
                $postfix = ".png";
            }
            if (!$postfix) {
                die(exif_imagetype($_FILES["picture_upload_" . $element->dependPicture]['tmp_name']));
                return $value = "#UNSET_DATA#";
            }

            if ($value && file_exists($_SERVER['DOCUMENT_ROOT'] . $value)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $value);
            }
            $value = "/" . $component->name . "/" . date("Y-m-d") . "/" . time() . rand(0, 1000) . $postfix;
            return $value = $value;
        } else if (!$element->dependPicture && isset($_FILES["picture_upload_" . $element->name]['tmp_name']) && $_FILES["picture_upload_" . $element->name]['tmp_name']) {
            $postfix = "";
            if (exif_imagetype($_FILES["picture_upload_" . $element->name]['tmp_name']) == IMAGETYPE_JPEG) {
                $postfix = ".jpg";
            } else if (exif_imagetype($_FILES["picture_upload_" . $element->name]['tmp_name']) == IMAGETYPE_GIF) {
                $postfix = ".gif";
            } else if (exif_imagetype($_FILES["picture_upload_" . $element->name]['tmp_name']) == IMAGETYPE_PNG) {
                $postfix = ".png";
            }
            if (!$postfix) {
                return $value = "#UNSET_DATA#";
            }
            if ($value && file_exists($_SERVER['DOCUMENT_ROOT'] . $value)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $value);
            }
            $value = "/" . $component->name . "/" . date("Y-m-d") . "/" . time() . rand(0, 1000) . $postfix;
            return $value = $value;
        } else if (!isset($_FILES[$element->name . "_upload"]["tmp_name"]) && !$element->dependPicture) {
// Если новой фотографии не добавлено и данная фотография ни от кого не зависит, то ничего не делать
            return $value = "#UNSET_DATA#";
        } else if ($element->dependPicture) {
// В противном же случае если данная фотография зависит от исходной закончить работу
            return $value = "#UNSET_DATA#";
        }

        return $value = "###ERROR###";
    }

    public static function prepareFilter($component, $parentElement, $element, $value) {
        
    }

    public static function render($application, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/picture.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($application, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        return "<img class=\"img-fluid img-thumbnail\" src=\"" . $data->{$element->name} . "\">";
    }

    public static function prepareDataAfterSave($component, $element, $value) {
// При закачивании новой фотографии
        if (isset($_FILES["picture_upload_" . $element->name]['tmp_name']) && $_FILES["picture_upload_" . $element->name]['tmp_name']) {
            // Отладочная информация
            error_log("Picture upload debug - Component: " . $component->name . ", Element: " . $element->name . ", Value: " . $value);
            error_log("File info: " . print_r($_FILES["picture_upload_" . $element->name], true));

            $postfix = "";
            if (exif_imagetype($_FILES["picture_upload_" . $element->name]['tmp_name']) == IMAGETYPE_JPEG) {
                $postfix = ".jpg";
            } else if (exif_imagetype($_FILES["picture_upload_" . $element->name]['tmp_name']) == IMAGETYPE_GIF) {
                $postfix = ".gif";
            } else if (exif_imagetype($_FILES["picture_upload_" . $element->name]['tmp_name']) == IMAGETYPE_PNG) {
                $postfix = ".png";
            }
            if (!$postfix) {
                return $result = 0;
            }

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/" . $component->name . "/" . date("Y-m-d");
            error_log("Attempting to create directory: " . $uploadDir);
            if (!is_dir($uploadDir)) {
                if (mkdir($uploadDir, 0755, true)) {
                    error_log("Directory created successfully: " . $uploadDir);
                } else {
                    error_log("Failed to create directory: " . $uploadDir);
                }
            } else {
                error_log("Directory already exists: " . $uploadDir);
            }
            move_uploaded_file($_FILES["picture_upload_" . $element->name]['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $value);
            
            if ($element->dependedPictures) {
                foreach ($element->dependedPictures as $dependedPicture) {
                    $depValue = $_POST[$dependedPicture];

                    copy($_SERVER['DOCUMENT_ROOT'] . $value, $_SERVER['DOCUMENT_ROOT'] . $depValue);

                    if ($_POST["picture_width_" . $dependedPicture])
                        img::imageresize($_SERVER['DOCUMENT_ROOT'] . $depValue, $_POST["picture_width_" . $dependedPicture], 0);
                    else if ($_POST["picture_height_" . $dependedPicture])
                        img::imageresize($_SERVER['DOCUMENT_ROOT'] . $depValue, 0, $_POST["picture_height_" . $dependedPicture]);
                    if ($_POST["picture_width_" . $dependedPicture] && $_POST["picture_height_" . $dependedPicture])
                        img::imageextendAndCut($_SERVER['DOCUMENT_ROOT'] . $depValue, $_POST["picture_width_" . $dependedPicture], $_POST["picture_height_" . $dependedPicture]);

                    if ($_POST["picture_mask_" . $dependedPicture]) {
                        img::imagemask($_SERVER['DOCUMENT_ROOT'] . $depValue, $_SERVER['DOCUMENT_ROOT'] . "/admin1/" . $_POST["picture_mask_" . $dependedPicture]);
                    }
                }
            }

            if ($_POST["picture_width_" . $element->name]) {
                img::imageresize($_SERVER['DOCUMENT_ROOT'] . $value, $_POST["picture_width_" . $element->name], 0);
            } else if ($_POST["picture_height_" . $element->name]) {
                img::imageresize($_SERVER['DOCUMENT_ROOT'] . $value, 0, $_POST["picture_height_" . $element->name]);
            }
            if ($_POST["picture_width_" . $element->name] && $_POST["picture_height_" . $element->name]) {
                img::imageextendAndCut($_SERVER['DOCUMENT_ROOT'] . $value, $_POST["picture_width_" . $element->name], $_POST["picture_height_" . $element->name]);
            } else if ($_POST["picture_width_" . $element->name]) {
                img::imageextend($_SERVER['DOCUMENT_ROOT'] . $value, $_POST["picture_width_" . $element->name], 0);
            } else if ($_POST["picture_height_" . $element->name]) {
                img::imageextend($_SERVER['DOCUMENT_ROOT'] . $value, 0, $_POST["picture_height_" . $element->name]);
            }
            if ($_POST["picture_mask_" . $element->name]) {
                img::imagemask($_SERVER['DOCUMENT_ROOT'] . $value, $_SERVER['DOCUMENT_ROOT'] . "/admin1/" . $_POST["picture_mask_" . $element->name]);
            }

            return $result = 0;
        }

// Если дана команда изменения размеров фотографии
        if ($element->dependedPictures) {
            foreach ($element->dependedPictures as $dependedPicture) {
                if (isset($_POST["picture_doResize_" . $dependedPicture]) && $_POST["picture_doResize_" . $dependedPicture] == "true") {
                    $x1 = $_POST["picture_x1_" . $dependedPicture];
                    $y1 = $_POST["picture_y1_" . $dependedPicture];
                    $x2 = $_POST["picture_x2_" . $dependedPicture];
                    $y2 = $_POST["picture_y2_" . $dependedPicture];

                    $depValue = $_POST[$dependedPicture];

                    copy($_SERVER['DOCUMENT_ROOT'] . $value, $_SERVER['DOCUMENT_ROOT'] . $depValue);

                    img::imagecrop($_SERVER['DOCUMENT_ROOT'] . $depValue, $x2, $y2, $x1, $y1);

                    if ($_POST["picture_width_" . $dependedPicture]) {
                        img::imageresize_i($_SERVER['DOCUMENT_ROOT'] . $depValue, $_POST["picture_width_" . $dependedPicture]);
                    } else if ($_POST["picture_height_" . $dependedPicture]) {
                        img::imageresize_i($_SERVER['DOCUMENT_ROOT'] . $depValue, $_POST["picture_height_" . $dependedPicture]);
                    }
                    if ($_POST["picture_width_" . $dependedPicture] && $_POST["picture_height_" . $dependedPicture]) {
                        img::imagecut($_SERVER['DOCUMENT_ROOT'] . $depValue, $_POST["picture_width_" . $dependedPicture], $_POST["picture_height_" . $dependedPicture]);
                    }
                    if ($_POST["picture_mask_" . $dependedPicture]) {
                        img::imagemask($_SERVER['DOCUMENT_ROOT'] . $depValue, $_SERVER['DOCUMENT_ROOT'] . "/admin1/" . $_POST["picture_mask_" . $dependedPicture]);
                    }
                }
            }
        }
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
