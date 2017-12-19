<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminTranslatableBundle\Doctrine\Admin;

use FSi\Bundle\AdminTranslatableBundle\Manager\LocaleManager;

interface TranslatableAwareElement
{
    public function setLocaleManager(LocaleManager $localeManager): void;
}
