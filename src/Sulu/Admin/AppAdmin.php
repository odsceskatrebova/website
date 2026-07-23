<?php

namespace App\Sulu\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class AppAdmin extends Admin
{
    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($navigationItemCollection->has('sulu_article.articles')) {
            $navigationItemCollection->remove('sulu_article.articles');
        }
    }
}
