<?php

declare(strict_types=1);

namespace App\Service;

use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Sulu\Snippet\Domain\Exception\SnippetNotFoundException;
use Sulu\Snippet\Domain\Repository\SnippetRepositoryInterface;

class SnippetService
{
    public function __construct(
        private readonly SnippetRepositoryInterface $snippetRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly ContentResolverInterface $contentResolver,
    ) {
    }

    /**
     * Load a snippet by UUID and locale and return its resolved content data.
     *
     * @return array<string, mixed>
     *
     * @throws SnippetNotFoundException
     */
    public function getSnippetContent(string $uuid, string $locale): array
    {
        $snippet = $this->snippetRepository->findOneBy(
            [
                'uuid' => $uuid,
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
                'version' => DimensionContentInterface::CURRENT_VERSION,
            ],
            [
                SnippetRepositoryInterface::SELECT_SNIPPET_CONTENT => [
                    DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_WEBSITE => true,
                ],
            ]
        );

        if (null === $snippet) {
            throw new SnippetNotFoundException($uuid);
        }

        $dimensionContent = $this->contentAggregator->aggregate(
            $snippet,
            [
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
                'version' => DimensionContentInterface::CURRENT_VERSION,
            ]
        );

        $resolved = $this->contentResolver->resolve($dimensionContent);

        return $resolved['content'];
    }
}
