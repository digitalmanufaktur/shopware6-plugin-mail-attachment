<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 */

namespace Dmf\MailAttachment\Content\MailTemplate\Service;

use Shopware\Core\Content\MailTemplate\Service\MailService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Dmf\MailAttachment\Extension\MailTemplateExtension;
use Dmf\MailAttachment\Installer\CustomFieldInstaller;
use Shopware\Core\Framework\Uuid\Uuid;
use function is_array;

class MailServiceDecorator extends MailService
{
    /**
     * @var MailService
     */
    private $innerService;

    /**
     * @var FilesystemInterface
     */
    private $filesystemPublic;

    /**
     * @var FilesystemInterface
     */
    private $filesystemPrivate;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var EntityRepositoryInterface
     */
    private $mediaRepository;

    public function __construct(
        MailService $innerService,
        FilesystemInterface $filesystemPublic,
        FilesystemInterface $filesystemPrivate,
        UrlGeneratorInterface $urlGenerator,
        EntityRepositoryInterface $mediaRepository
    ) {
        $this->innerService = $innerService;
        $this->filesystemPublic = $filesystemPublic;
        $this->filesystemPrivate = $filesystemPrivate;
        $this->urlGenerator = $urlGenerator;
        $this->mediaRepository = $mediaRepository;
    }

    public function send(array $data, Context $context, array $templateData = []): ?\Swift_Message
    {
        $extensionName = MailTemplateExtension::EXTENSION_NAME;
        if (!$context->hasExtension($extensionName)) {
            return $this->innerService->send($data, $context, $templateData);
        }
        $customFields = $context->getExtension($extensionName)
            ->getMailTemplate()
            ->getCustomFields();
        if (is_array($customFields)) {
            $mediaIds = [];
            $customFieldKeys = [
                CustomFieldInstaller::CUSTOM_FIELD_MEDIA1,
                CustomFieldInstaller::CUSTOM_FIELD_MEDIA2,
                CustomFieldInstaller::CUSTOM_FIELD_MEDIA3,
            ];
            foreach ($customFieldKeys as $customFieldKey) {
                if (!isset($customFields[$customFieldKey])) {
                    continue;
                }
                if (\is_string($customFields[$customFieldKey]) && Uuid::isValid($customFields[$customFieldKey])) {
                    $mediaIds[] = $customFields[$customFieldKey];
                }
            }
            if ($mediaIds) {
                $binAttachments = $this->prepareBinAttachments($mediaIds, $context);
                if ($binAttachments) {
                    $dataKey = 'binAttachments';
                    if (isset($data[$dataKey]) && is_array($data[$dataKey]) && $data[$dataKey]) {
                        $data[$dataKey] = \array_merge($data[$dataKey], $binAttachments);
                    } else {
                        $data[$dataKey] = $binAttachments;
                    }
                }
            }
        }
        return $this->innerService->send($data, $context, $templateData);
    }

    private function prepareBinAttachments(array $mediaIds, Context $context): array
    {
        $medias = $this->mediaRepository->search((new Criteria($mediaIds))->addAssociation('mediaFolder'), $context);
        $binAttachments = [];
        if ($medias->count() > 0) {
            foreach ($medias as $media) {
                $path = $this->urlGenerator->getRelativeMediaUrl($media);
                $binAttachments[] = [
                    'content'  => $this->getFileSystem($media)->read($path),
                    'fileName' => \basename($path),
                    'mimeType' => $media->getMimeType(),
                ];
            }
        }
        return $binAttachments;
    }

    private function getFileSystem(MediaEntity $media): FilesystemInterface
    {
        if ($media->isPrivate()) {
            return $this->filesystemPrivate;
        }
        return $this->filesystemPublic;
    }
}
