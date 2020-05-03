<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 */

namespace Dmf\MailAttachment\Extension;

use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;

class MailTemplateExtension extends Struct
{
    public const EXTENSION_NAME = 'dmf_mail_template';

    /**
     * @var MailTemplateEntity|null
     */
    private $mailTemplate = null;

    public function getMailTemplate(): ?MailTemplateEntity
    {
        return $this->mailTemplate;
    }

    public function setMailTemplate(?MailTemplateEntity $mailTemplate = null): self
    {
        $this->mailTemplate = $mailTemplate;
        return $this;
    }
}
