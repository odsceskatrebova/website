<?php

declare(strict_types=1);

namespace App\Sulu\Admin;

use App\Entity\CookieConsent;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class CookieConsentAdmin extends Admin
{
    public const LIST_VIEW = 'app.cookieConsent.list';
    public const EDIT_FORM_VIEW = 'app.cookieConsent.edit_form';
    public const EDIT_FORM_DETAILS_VIEW = 'app.cookieConsent.edit_form.details';

    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(CookieConsent::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $rootItem = new NavigationItem('app.cookieConsents');
            $rootItem->setIcon('su-earth');
            $rootItem->setPosition(30);
            $rootItem->setView(static::LIST_VIEW);

            $navigationItemCollection->add($rootItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $listToolbarActions = [];

        if ($this->securityChecker->hasPermission(CookieConsent::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }

        if ($this->securityChecker->hasPermission(CookieConsent::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/cookieConsents')
                    ->setResourceKey(CookieConsent::RESOURCE_KEY)
                    ->setListKey(CookieConsent::LIST_KEY)
                    ->setTitle('app.cookieConsents')
                    ->addListAdapters(['table'])
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/cookieConsents/:id')
                    ->setResourceKey(CookieConsent::RESOURCE_KEY)
                    ->setBackView(static::LIST_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_DETAILS_VIEW, '/details')
                    ->setResourceKey(CookieConsent::RESOURCE_KEY)
                    ->setFormKey(CookieConsent::FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->addToolbarActions([])
                    ->setParent(static::EDIT_FORM_VIEW)
            );
        }
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'CookieConsents' => [
                    CookieConsent::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }
}
