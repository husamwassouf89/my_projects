<?php


namespace App\Traits;


trait MathKit
{
    function matrixMultiplication($m1, $m2): array
    {
        $r = count($m1);
        $c = count($m2[0]);
        $p = count($m2);
        if (count($m1[0]) != $p) {
            throw new \Exception('Incompatible matrices');
        }
        $m3 = array();
        for ($i = 0; $i < $r; $i++) {
            for ($j = 0; $j < $c; $j++) {
                $m3[$i][$j] = 0;
                for ($k = 0; $k < $p; $k++) {
                    $m3[$i][$j] += $m1[$i][$k] * $m2[$k][$j];
                }
            }
        }
        return ($m3);
    }

    function linearRegression($x, $y): array
    {

        // calculate number points
        $n = count($x);

        // ensure both arrays of points are the same size
        if ($n != count($y)) {

            trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);

        }

        // calculate sums
        $x_sum = array_sum($x);
        $y_sum = array_sum($y);

        $xx_sum = 0;
        $xy_sum = 0;

        for ($i = 0; $i < $n; $i++) {

            $xy_sum += ($x[$i] * $y[$i]);
            $xx_sum += ($x[$i] * $x[$i]);

        }

        // calculate slope
        $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

        // calculate intercept
        $b = ($y_sum - ($m * $x_sum)) / $n;

        // return result
        return array("m" => $m, "b" => $b);

    }
}
