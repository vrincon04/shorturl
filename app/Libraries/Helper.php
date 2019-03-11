<?php

namespace App\Libraries;

class Helper {
    /**
     *  Converts an integer into the alphabet base (A-z).
     * 
     * @param int $number This is the number to convert.
     * 
     * @return string
     */
    public static function numberToAlphabet($number)
    {
        // Create and fill an array with alphabet(A-Za-z).
        $alphabet = array_merge(range('A', 'Z'), range('a', 'z'));
        //Assign the size of $alphabet array to the variable $length.
        $length = count($alphabet);
       //This variable will hold the output generated.
        $result = '';
        //Make sure the parameter is greater than or equal to zero so we can star lopping.
        for ($i = 1; $number >= 0; $i++) {
            // The code below limits the number to the $alphabet array size
            $formula = abs(($number % pow($length, $i) / pow($length, $i - 1)));
            // Comcat the current result with the previous
            $result = $alphabet[$formula] . $result;
            // Reduce the number with the size of the array raised to the iteration
            $number -= pow($length, $i);
        }

        return $result;
    }
}