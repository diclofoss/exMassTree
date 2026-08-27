<?php

class utils {

    static function requireToVar($file) {
        ob_start();
        require($file);
        return ob_get_clean();
    }

    static function pretext($text, $col = 40) {
        $text = strip_tags($text);
        $text = str_replace("\"", "", $text);
        $text = str_replace("\'", "", $text);
        if (strlen($text) > $col) {
            $text = substr($text, 0, $col);
            $text = preg_replace("/([^ ]+)$/", "", $text);
            $text = substr($text, 0, strlen($text) - 1) . "...";
        }

        return $text;
    }

    static function findElement($elements, $elementName) {
        foreach ($elements as $curElement) {
            if ($curElement->name == $elementName) {
                return $curElement;
            }
            if (isset($curElement->elements)) {
                $curEl = utils::findElement($curElement->elements, $elementName);
                if ($curEl) {
                    return $curEl;
                }
            }
        }
        return "";
    }

    static function findElementInContext($elements, $elementName, $contextName) {
        if ($contextName) {
            $context = utils::findElement($elements, $contextName);
            if ($context && isset($context->elements)) {
                $found = utils::findElement($context->elements, $elementName);
                if ($found) {
                    return $found;
                }
            }
        }
        return utils::findElement($elements, $elementName);
    }

    static function findParentElement($elements, $elementName) {
        foreach ($elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            if (!isset($curElement->elements)) {
                continue;
            }
            foreach ($curElement->elements as $curEl) {
                if ($curEl->type == "fieldset") {
                    continue;
                }
                if ($curEl->name == $elementName) {
                    return $curElement;
                }
                if (isset($curEl->elements)) {
                    $el = utils::findParentElement($curElement->elements, $elementName);
                    if ($el) {
                        return $el;
                    }
                }
            }
        }
        return "";
    }

    // Вычисление полного пути к элементу (массив имен элементов от корня)
    static function findElementPath($elements, $elementName, $currentPath = array()) {
        foreach ($elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            $path = array_merge($currentPath, array($curElement->name));
            if ($curElement->name == $elementName) {
                return $path;
            }
            if (isset($curElement->elements)) {
                $foundPath = utils::findElementPath($curElement->elements, $elementName, $path);
                if ($foundPath) {
                    return $foundPath;
                }
            }
        }
        return null;
    }

}

class WidgetUtils {

    // Генерация уникального widgetId с учетом вложенности
    static function generateWidgetId($category, $component, $elementPath) {
        // $elementPath = ['element1', 'element2', 'element3']
        // Используем специальный разделитель для избежания конфликтов с подчеркиваниями в именах
        $parts = array_merge([$category, $component], $elementPath);
        $path = implode('::', $parts); // Используем :: как разделитель
        $hash = substr(md5($path), 0, 8); // Короткий хеш для уникальности
        return 'widget_' . base64_encode($path) . '_' . $hash;
    }

    // Получить состояние виджета из cookies
    static function getWidgetState($widgetId, $default = null) {
        // Кодируем widgetId для поиска в cookies
        $cookieName = self::encodeCookieName($widgetId . '_state');
        if (isset($_COOKIE[$cookieName])) {
            $decoded = json_decode($_COOKIE[$cookieName], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return $default !== null ? $default : array('page' => 1, 'filters' => array(), 'frontFilters' => array(), 'textsearch' => '');
    }

    // Сохранить состояние виджета в cookies
    static function setWidgetState($widgetId, $state) {
        // Кодируем widgetId для использования в имени cookie (убираем недопустимые символы)
        $cookieName = self::encodeCookieName($widgetId . '_state');
        $value = json_encode($state);
        setcookie($cookieName, $value, time() + (86400 * 30), '/'); // 30 дней
        $_COOKIE[$cookieName] = $value; // Для текущего запроса
    }
    
    // Кодировать имя cookie (убрать недопустимые символы)
    static function encodeCookieName($name) {
        // Заменяем недопустимые символы на безопасные
        return str_replace(['=', ',', ';', ' ', "\t", "\r", "\n", "\013", "\014"], '_', base64_encode($name));
    }
    
    // Декодировать имя cookie
    static function decodeCookieName($encodedName) {
        return base64_decode(str_replace('_', '=', $encodedName));
    }

    // Получить значение из состояния
    static function getWidgetCookie($widgetId, $key, $default = null) {
        $state = self::getWidgetState($widgetId);
        return isset($state[$key]) ? $state[$key] : $default;
    }

    // Установить значение в состоянии
    static function setWidgetCookie($widgetId, $key, $value) {
        $state = self::getWidgetState($widgetId);
        $state[$key] = $value;
        self::setWidgetState($widgetId, $state);
    }

    // Парсинг widgetId для получения пути
    static function parseWidgetId($widgetId) {
        // widget_base64encoded_path_hash
        if (preg_match('/^widget_(.+)_[a-f0-9]{8}$/', $widgetId, $matches)) {
            $encodedPath = $matches[1];
            $path = base64_decode($encodedPath);
            if ($path !== false) {
                $pathParts = explode('::', $path); // Используем :: как разделитель
                if (count($pathParts) >= 2) {
                    return array(
                        'category' => $pathParts[0],
                        'component' => $pathParts[1],
                        'elementPath' => array_slice($pathParts, 2)
                    );
                }
            }
        }
        return null;
    }

}