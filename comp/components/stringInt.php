<?php

include_once 'stringText.php';

class stringInt extends stringText {

    public static function getDataType($element) {
        return "int(11)";
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        if (!isset($data->{$element->name})) {
            return "";
        }
        $valueShown = $data->{$element->name};
        if (isset($element->format)) {
            if (class_exists('NumberFormatter')) {
                // Use NumberFormatter if intl extension is available
                $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
                $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
                $valueShown = $fmt->formatCurrency(intval($valueShown), $element->format);
            } else {
                // Fallback to number_format if intl extension is not available
                $formatted = number_format(intval($valueShown), 0, '.', ' ');
                $valueShown = $formatted . ' ' . $element->format;
            }
        }
        if (isset($element->customCSS)) {
            foreach ($element->customCSS as $customCssName => $customCssVal) {
                if ($customCssName == "rest") {
                    continue;
                }
                list($cmd, $val) = preg_split("/ /", $customCssName);
                if ($cmd == "lt") {
                    if ($val >= $data->{$element->name}) {
                        return "<span style=\"$customCssVal\">$valueShown</span>";
                    }
                }
            }
            if (isset($element->customCSS->rest)) {
                return "<span style=\"{$element->customCSS->rest}\">$valueShown</span>";
            }
        }
        return $valueShown;
    }

}
