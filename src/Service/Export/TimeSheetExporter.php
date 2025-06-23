<?php

namespace App\Service\Export;

use App\Entity\WorkMonth;

readonly class TimeSheetExporter
{
    public function __construct(
        private TimeSheetBuilder $builder,
        private SpreadsheetWriterInterface $writer,
        private FileNamer $fileNamer,
        private DirectoryManager $directoryManager
    ) {}

    public function export(WorkMonth $workMonth): string
    {
        $this->directoryManager->ensureExists();
        $path = $this->directoryManager->getPath() . $this->fileNamer->generate($workMonth);
        $this->writer->write($this->builder->build($workMonth), $path);

        return $path;
    }
}
