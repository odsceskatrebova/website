<?php

declare(strict_types=1);

namespace App\Sulu\Admin;

use Robole\SuluAITranslatorBundle\Admin\AITranslatorAdmin;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class AITranslatorAdminDecorator extends AITranslatorAdmin
{
    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
    ) {
        parent::__construct($viewBuilderFactory, $securityChecker);
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        // Register the config view (Settings > Translation Config) but skip toolbar actions
        if ($this->securityChecker->hasPermission(self::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createViewBuilder(
                    self::TRANSLATION_CONFIG_VIEW,
                    '/translation',
                    self::TRANSLATION_CONFIG_VIEW
                )
            );
        }
    }
}
