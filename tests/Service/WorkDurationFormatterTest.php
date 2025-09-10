<?php

namespace App\Tests\Service;

use App\Service\WorkDurationFormatter;
use PHPUnit\Framework\TestCase;

class WorkDurationFormatterTest extends TestCase
{
    private WorkDurationFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new WorkDurationFormatter();
    }

    public function testFormatWithNullDuration(): void
    {
        $this->assertSame('-', $this->formatter->format(null));
    }

    public function testFormatWithZeroDuration(): void
    {
        $this->assertSame('-', $this->formatter->format(0));
    }

    public function testFormatWithNegativeDuration(): void
    {
        $this->assertSame('-', $this->formatter->format(-20));
    }

    public function testFormatWith90Minutes(): void
    {
        $this->assertSame('1H30', $this->formatter->format(90));
    }

    public function testFormatWith240Minutes(): void
    {
        $this->assertSame('4H00', $this->formatter->format(240));
    }

    public function testFormatWith170Minutes(): void
    {
        $this->assertSame('2H50', $this->formatter->format(170));
    }
}
