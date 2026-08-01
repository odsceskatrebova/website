<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ElectionDateRangeExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('election_date_range', $this->formatElectionDateRange(...)),
        ];
    }

    public function formatElectionDateRange(
        \DateTimeInterface|string|null $start,
        \DateTimeInterface|string|null $end,
    ): string {
        $startDate = $this->toDate($start);
        $endDate = $this->toDate($end);

        if ($startDate === null && $endDate === null) {
            return '';
        }

        if ($startDate === null || $endDate === null || $startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            return $this->formatFull($startDate ?? $endDate);
        }

        if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
            return \sprintf('%d.–%s', (int) $startDate->format('j'), $this->formatFull($endDate));
        }

        if ($startDate->format('Y') === $endDate->format('Y')) {
            return \sprintf(
                '%d. %d. – %s',
                (int) $startDate->format('j'),
                (int) $startDate->format('n'),
                $this->formatFull($endDate),
            );
        }

        return \sprintf('%s – %s', $this->formatFull($startDate), $this->formatFull($endDate));
    }

    private function formatFull(\DateTimeInterface $date): string
    {
        return \sprintf('%d. %d. %s', (int) $date->format('j'), (int) $date->format('n'), $date->format('Y'));
    }

    private function toDate(\DateTimeInterface|string|null $value): ?\DateTimeInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
