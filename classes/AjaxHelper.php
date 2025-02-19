<?php

function calculate_percentage($value, $total): int
{
    return $total != 0 ? ((int)(round(($value / $total) * 100))) : 0;
}