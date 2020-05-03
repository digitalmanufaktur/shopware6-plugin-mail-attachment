<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 */

namespace Dmf\MailAttachment\Content\MailTemplate\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Event\BusinessEvent;
use Shopware\Core\Content\MailTemplate\MailTemplateActions;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Event\MailActionInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Dmf\MailAttachment\Extension\MailTemplateExtension;
use Shopware\Core\Content\MailTemplate\Exception\MailEventConfigurationException;
use Shopware\Core\Content\MailTemplate\Exception\SalesChannelNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use function get_class;

class MailSendSubscriberDecorator implements EventSubscriberInterface
{
    public const ACTION_NAME = MailTemplateActions::MAIL_TEMPLATE_MAIL_SEND_ACTION;

    /**
     * @var EventSubscriberInterface
     */
    private $innerService;

    /**
     * @var EntityRepositoryInterface
     */
    private $mailTemplateRepository;

    public function __construct(
        EventSubscriberInterface $innerService,
        EntityRepositoryInterface $mailTemplateRepository
    ) {
        $this->innerService = $innerService;
        $this->mailTemplateRepository = $mailTemplateRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            self::ACTION_NAME => 'sendMail',
        ];
    }

    /**
     * @throws MailEventConfigurationException
     * @throws SalesChannelNotFoundException
     * @throws InconsistentCriteriaIdsException
     * @see \Shopware\Core\Content\MailTemplate\Subscriber\MailSendSubscriber
     */
    public function sendMail(BusinessEvent $event): void
    {
        $mailEvent = $event->getEvent();
        if (!$mailEvent instanceof MailActionInterface) {
            throw new MailEventConfigurationException('Not an instance of MailActionInterface', get_class($mailEvent));
        }
        $context = $event->getContext();
        $idFilter = new EqualsFilter('mailTemplateTypeId', $this->getMailTemplateTypeId($event));
        $criteria = (new Criteria())->addFilter($idFilter)->setLimit(1);
        if ($mailEvent->getSalesChannelId()) {
            $criteria->addFilter(
                new EqualsFilter('mail_template.salesChannels.salesChannel.id', $mailEvent->getSalesChannelId())
            );
            /** @var MailTemplateEntity|null $mailTemplate */
            $mailTemplate = $this->mailTemplateRepository->search($criteria, $context)->first();
            if (!$mailTemplate) {
                $criteria = (new Criteria())->addFilter($idFilter)->setLimit(1);
                /** @var MailTemplateEntity|null $mailTemplate */
                $mailTemplate = $this->mailTemplateRepository->search($criteria, $context)->first();
            }
        } else {
            /** @var MailTemplateEntity|null $mailTemplate */
            $mailTemplate = $this->mailTemplateRepository->search($criteria, $context)->first();
        }
        if (!$mailTemplate) {
            $this->innerService->sendMail($event);
            return;
        }
        $extensionName = MailTemplateExtension::EXTENSION_NAME;
        if ($context->hasExtension($extensionName)) {
            $extension = $context->getExtension($extensionName);
        } else {
            $extension = new MailTemplateExtension();
        }
        $extension->setMailTemplate($mailTemplate);
        $context->addExtension($extensionName, $extension);
        $this->innerService->sendMail($event);
    }

    /**
     * @throws MailEventConfigurationException
     */
    private function getMailTemplateTypeId(BusinessEvent $event): string
    {
        $config = $event->getConfig();
        $key = 'mail_template_type_id';
        if (!isset($config[$key]) || !\array_key_exists($key, $config)) {
            throw new MailEventConfigurationException("Configuration $key is missing.", get_class($event->getEvent()));
        }
        return $config[$key];
    }
}
