<?php

use PHPUnit\Framework\TestCase;

class TeacherServiceMarkFormulaTest extends TestCase
{
    public function test_example_formula(): void
    {
        $provinceMark = 10;
        $schoolMark = 6;
        $score = ($provinceMark * 62 / 12) + ($schoolMark * 39 / 12);
        $this->assertSame(71.17, round($score, 2));
    }
}
