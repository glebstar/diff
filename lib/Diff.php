<?php

class Diff {
    /**
     * Возвращает текст с пометками о изменениях второго текста относительно первого
     * @param type $t1 Первый текст
     * @param type $t2 Второй текст
     * 
     * @return string Измененный текст с пометками
     */
    public static function getDiff($t1, $t2) {
        // небольшая обработка, чтобы при preg_split не потерять исходные переносы строк и точки
        $t1 = preg_replace('/(\r\n|\n)/', '&nbsp;<br />--br--', $t1);
        $t1 = preg_replace('/(\.\s|!+|\?+)/', '$1&nbsp;--dot--', $t1);
        
        $t2 = preg_replace('/(\r\n|\n)/', '&nbsp;<br />--br--', $t2);
        $t2 = preg_replace('/(\.\s|!+|\?+)/', '$1&nbsp;--dot--', $t2);
        
        $arr1 = preg_split('/--br--|--dot--/', $t1);        
        $arr2 = preg_split('/--br--|--dot--/', $t2); 
        
        if(count($arr1) == 1 && !$arr1[0]) {
            $arr1 = array();
        }
        
        if(count($arr2) == 1 && !$arr2[0]) {
            $arr2 = array();
        }
        
        // результирующий массив
        $arr3 = array();
        
        $countArr1 = count($arr1);
        $currItemArr1 = 0;
        
        // количество удаленных
        $delCnt = 0;
        
        for($i=0; $i<count($arr2); $i++) {
            $arr2[$i] = trim($arr2[$i]);
            
            if($currItemArr1 >= $countArr1) {
                // первый массив закончился, остались только новые предложения
                $arr3[] = array(
                    'mark' => 'new',
                    'value' => $arr2[$i]
                );
                
                continue;
            }
            
            $arr1[$currItemArr1] = trim($arr1[$currItemArr1]);

            if($arr2[$i] == $arr1[$currItemArr1]) {
                $arr3[] = array(
                    'mark' => 'no',
                    'value' => $arr2[$i]
                );
                $currItemArr1++;
            } else {
                // нужно определить новое предложение или измененное
                if(self::_isDiff($arr2[$i], $arr1[$currItemArr1])) {
                    // измененное
                    $arr3[] = array(
                        'mark' => 'diff',
                        'value' => $arr2[$i],
                        'oldvalue' => $arr1[$currItemArr1]
                    );
                    $currItemArr1++;
                } else {
                    // опредилить удаленное предложение
                    // если в следующих трех предложениях первого текста
                    // встречается текущее предложение, то считать его удаленным

                    $isDel = false;
                    for($j=$currItemArr1; $j<=$currItemArr1+2; $j++) {
                        if($j >= $countArr1) {
                            break;
                        }
                        if($arr2[$i] == $arr1[$j] || self::_isDiff($arr2[$i], $arr1[$j])) {
                            // удаленное предложение
                            $arr3[] = array(
                                'mark' => 'del',
                                'value' => $arr1[$currItemArr1]
                            );
                            $currItemArr1++;
                            $i--;
                            $delCnt++;
                            $isDel = true;
                            break;
                        }
                    }

                    if($isDel) {
                        continue;
                    }

                    // новое
                    $arr3[] = array(
                        'mark' => 'new',
                        'value' => $arr2[$i]
                    );
                }
            }
        }
        
        // если первый массив (минус количество удаленных) длиннее чем второй
        // все непроверенные строки из первого считать удаленными
        for($i=count($arr2); $i<count($arr1)-$delCnt; $i++) {
            $arr3[] = array(
                'mark' => 'del',
                'value' => trim($arr1[$i+$delCnt])
            );
        }
        
        return self::_getResult($arr3);
    }
    
    /**
     * Проверяет, является ли строка измененной
     * Используется упрощенный алгоритм "шинглов"
     * 
     * @param type $t1 Первый текст
     * @param type $t2 Второй текст
     * @return boolean
     */
    private static function _isDiff($t1, $t2) {
        // разбить оба предложения на слова
        // если во втором предложении 33 или более процента слов совпадают с первым
        // считать предложение измененным, иначе - нет
        
        // очистить предложения от знаков препинания
        // и лишних тегов
        $t1 = preg_replace('/\,|;|:|\.|&nbsp;\<br \/\>/', '', $t1);
        $t2 = preg_replace('/\,|;|:|\.|&nbsp;\<br \/\>/', '', $t2);
        
        if((!$t1 && $t2) || (!$t2 && $t1)) {
            return false;
        }
        
        $arr1 = explode(' ', $t1);
        $arr2 = explode(' ', $t2);
        
        // приведем первый массив к нижнему регистру, чтобы сравнивать без учета регистра
        // с учетом приведенных примеров текстов на производительность это фактически не повлияет
        for($i=0; $i<count($arr1); $i++) {
            $arr1[$i] = mb_strtolower($arr1[$i], 'UTF-8');
        }
        
        $res = 0;
        
        foreach ($arr2 as $item) {
            if(!$item || preg_match('/^\s+$/', $item)) {
                continue;
            }
            
            if(in_array(mb_strtolower($item, 'UTF-8'), $arr1)) {
                $res++;
            }
        }
        
        // процент совпадения можно в дальнейшем откорректировать
        if((100 / count($arr2) * $res) >= 33 ) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Возвращает подготовленную к выводу в браузер строку
     * с отмеченными новыми, измененными и удаленными предложениями
     * 
     * @param type $arr Массив предложений.
     * @return string
     */
    private static function _getResult($arr) {
        $res = '';
        
        foreach ($arr as $item) {
            switch ($item['mark']) {
                case 'diff':
                    $res .= '<span class="diff" data-old="' . $item['oldvalue'] . '">' . $item['value'] . '</span>';
                    break;
                case 'new':
                    $res .= '<span class="new">' . $item['value'] . '</span>';
                    break;
                case 'del':
                    $res .= '<span class="del">' . $item['value'] . '</span>';
                    break;
                default :
                    $res .= $item['value'];
            }
        }
        
        return $res;
    }
}
