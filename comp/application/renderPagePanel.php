<?php

// -*- coding: cp1251; -*-
// Вычисляем возможные начальную и конечную страницы
$page_from = $curpage - 3;
$page_to = $curpage + 2;

// Вычисляем максимальное число страниц и убираем дробный остаток
$page_max = ceil($total / $limit);
if (!is_numeric($page_max))
    list($page_max, $rest) = preg_split("/\./", $page_max);
else
    $rest = 0;

// Если остаток остается, то округляем в большую сторону
if ($rest)
    $page_max++;

// Если первая страница меньше нуля то сдвинуть все страницы вправо. Было: -2, -1, 0, 1 ,2 Стало: 0, 1, 2, 3, 4
if ($page_from < 0) {
    $page_to = $page_to - $page_from;
    $page_from = 0;
}

// Обратный процесс, если максимальное число страниц меньше чем получилось то сдвинуть влево.
if ($page_to > $page_max) {
    $page_from = $page_from + round($page_max - $page_to);
    $page_to = $page_max;
}

// Если после последнего сдвига опять уехала первая страница её вернуть на место.
if ($page_from < 0)
    $page_from = 0;



// Если первая страница не равна нулю, то нужно вначале сделать многоточее типа 1...3 4 5 но если первая страница будет 2 этого делать не нужно
if ($page_from != 0) {
    if ($page_from != 1)
        $mnogotochee = "<span class=\"btn\">..</span>";
    else
        $mnogotochee = " ";
    $page = 1;
    $cur_link = str_replace("#link#", $page, $link);
    echo "<a page=\"$page\" parentElement=\"$parentElementName\" element=\"$elementName\" class=\"pager btn\" href=\"$cur_link\" title=\"Перейти на страницу: $page\">$page</a>$mnogotochee";
}

// Выводим набор основных символов
for ($i = $page_from; $i < $page_to; $i++) {
    $page = ceil($i + 1);
    if ($curpage != $page) {
        $cur_link = str_replace("#link#", $page, $link);
        echo "<a page=\"$page\" parentElement=\"$parentElementName\" element=\"$elementName\" class=\"pager btn\" href=\"$cur_link\" title=\"Перейти на страницу: $page\">$page</a>";
    } else
        echo "<span style=\"color:blue\" class=\"btn\">$page</span>";
}

// Если последняя страница больше того, что нужно вывести на экран, то сделать многоточие типа 3 4 5....10, но если это предпоследняя страница, этого делать не стоит
if ($page_to < $page_max) {
    if (($page_max - 1) != $i)
        $mnogotochee = "<span class=\"btn\">..</span>";
    else
        $mnogotochee = " ";

    $page = $page_max;

    $cur_link = str_replace("#link#", $page, $link);
    echo "$mnogotochee<a page=\"$page\" parentElement=\"$parentElementName\" element=\"$elementName\" class=\"pager btn\" href=\"$cur_link\" title=\"Перейти на страницу: $page\">$page</a>";
}
?>