<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 */

namespace Dmf\MailAttachment;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Dmf\MailAttachment\Installer\CustomFieldInstaller;

class DmfMailAttachment extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context): void
    {
        (new CustomFieldInstaller($this->container))->install($context);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context): void
    {
        (new CustomFieldInstaller($this->container))->uninstall($context);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context): void
    {
        (new CustomFieldInstaller($this->container))->update($context);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context): void
    {
        (new CustomFieldInstaller($this->container))->activate($context);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
        (new CustomFieldInstaller($this->container))->deactivate($context);
    }
}
