<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 */

namespace Dmf\MailAttachment\Installer;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class CustomFieldInstaller implements InstallerInterface
{
    public const CUSTOM_FIELD_MEDIA1 = 'dmf_mail_attachment_media1';
    public const CUSTOM_FIELD_MEDIA2 = 'dmf_mail_attachment_media2';
    public const CUSTOM_FIELD_MEDIA3 = 'dmf_mail_attachment_media3';

    /**
     * @var EntityRepositoryInterface
     */
    private $customFieldRepository;

    public function __construct(ContainerInterface $container)
    {
        $this->customFieldRepository = $container->get('custom_field.repository');
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context): void
    {
        $context = $context->getContext();
        $this->customFieldRepository->upsert([[
            'id'     => 'faa4692ea2fa42d581623bbaf6217095',
            'name'   => static::CUSTOM_FIELD_MEDIA1,
            'type'   => CustomFieldTypes::TEXT,
            'active' => true,
        ]], $context);
        $this->customFieldRepository->upsert([[
            'id'     => '733858927b074dd69feab2db21e6ad94',
            'name'   => static::CUSTOM_FIELD_MEDIA2,
            'type'   => CustomFieldTypes::TEXT,
            'active' => true,
        ]], $context);
        $this->customFieldRepository->upsert([[
            'id'     => '9f053a9df86c470d886f58e9454054f7',
            'name'   => static::CUSTOM_FIELD_MEDIA3,
            'type'   => CustomFieldTypes::TEXT,
            'active' => true,
        ]], $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
    }
}
