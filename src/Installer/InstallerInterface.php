<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 */

namespace Dmf\MailAttachment\Installer;

use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

interface InstallerInterface
{
    /**
     * Install
     *
     * @param InstallContext $context
     * @return void
     */
    public function install(InstallContext $context): void;

    /**
     * Update
     *
     * @param UpdateContext $context
     * @return void
     */
    public function update(UpdateContext $context): void;

    /**
     * Uninstall
     *
     * @param UninstallContext $context
     * @return void
     */
    public function uninstall(UninstallContext $context): void;

    /**
     * Activate
     *
     * @param ActivateContext $context
     * @return void
     */
    public function activate(ActivateContext $context): void;

    /**
     * Deactivate
     *
     * @param DeactivateContext $context
     * @return void
     */
    public function deactivate(DeactivateContext $context): void;
}
