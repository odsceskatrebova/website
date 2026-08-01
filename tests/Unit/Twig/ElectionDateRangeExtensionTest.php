<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig;

use App\Twig\ElectionDateRangeExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ElectionDateRangeExtensionTest extends TestCase
{
    private ElectionDateRangeExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new ElectionDateRangeExtension();
    }

    /**
     * @return array<string, array{0: \DateTimeInterface|string|null, 1: \DateTimeInterface|string|null, 2: string}>
     */
    public static function provideRanges(): array
    {
        return [
            'two days in same month' => ['2026-09-25', '2026-09-26', '25.–26. 9. 2026'],
            'cross-month range' => ['2026-09-30', '2026-10-01', '30. 9. – 1. 10. 2026'],
            'cross-year range' => ['2026-12-31', '2027-01-01', '31. 12. 2026 – 1. 1. 2027'],
            'end missing' => ['2026-09-26', null, '26. 9. 2026'],
            'end equals start' => ['2026-09-26', '2026-09-26', '26. 9. 2026'],
            'start missing' => [null, '2026-09-26', '26. 9. 2026'],
            'both missing' => [null, null, ''],
            'datetime objects' => [new \DateTimeImmutable('2026-09-25'), new \DateTimeImmutable('2026-09-26'), '25.–26. 9. 2026'],
        ];
    }

    #[DataProvider('provideRanges')]
    public function testFormatsRange(\DateTimeInterface|string|null $start, \DateTimeInterface|string|null $end, string $expected): void
    {
        self::assertSame($expected, $this->extension->formatElectionDateRange($start, $end));
    }
}
