<?php

namespace App\Tests\Service\Helper;

use App\Service\Helper\AttachmentCleaner;
use PHPUnit\Framework\TestCase;

class AttachmentCleanerTest extends TestCase
{
    public function testCleanupDeletesFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'testfile');
        file_put_contents($file, 'test');

        $this->assertFileExists($file);

        $cleaner = new AttachmentCleaner();
        $cleaner->cleanup($file);

        $this->assertFileDoesNotExist($file);
    }

    public function testCleanupHandlesNullPath(): void
    {
        $cleaner = new AttachmentCleaner();
        $cleaner->cleanup(null);

        $this->assertTrue(true);
    }
}
