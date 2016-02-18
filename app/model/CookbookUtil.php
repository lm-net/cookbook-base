<?php

namespace App\Model;

/**
 * Třída s pomocnými konstantami, funkcemi.
 */
class CookbookUtil {

    public static $CZECH_LETTERS = array('a', 'b', 'c', 'č', 'd', 'e', 'f', 'g', 'h', 'ch', 'i', 'j', 'k',
        'l', 'm', 'n', 'o', 'p', 'q', 'r', 'ř', 's', 'š', 't', 'u', 'v',
        'w', 'x', 'y', 'z', 'ž');

    public static function to_number($text_representation) {
        return preg_replace('/,/', '.', $text_representation);
    }
}
