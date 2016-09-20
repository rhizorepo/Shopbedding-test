<?php
class BorderJump_ApiClient_Helper_Data
{
    /**
     * Recursive `ksort`
     * 
     * @author tannern@gmail.com
     * @param array $array The array to be sorted
     * @param bool $copy If copy then duplicate and modify $array else modify
     *                   $array
     * @param int $sortFlags You may modify the behavior of the sort using the
     *                       optional parameter sort_flags, for details see
     *                       sort()
     * @return  bool|array If $copy then sorted array else ksort return value
     */
    public function kSortRecursive(&$array, $copy = false, $sortFlags = SORT_REGULAR) {
        if ( ! is_array($array)) {
            return;
        }
        
        if ( $copy ) {
            $a = $array;
        } else {
            $a =& $array;
        }
        foreach ( $a as $k => $v ) {
            if ( !is_array($v) ) { continue; }
            self::kSortRecursive($a[$k]);
        }
        $b = ksort($a, $sortFlags);
        return ( $copy ) ? $a : $b;
    }
}