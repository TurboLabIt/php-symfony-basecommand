<?php
namespace TurboLabIt\BaseCommand\Service;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;


class Mailer
{
    protected TemplatedEmail $email;
    protected bool $disableAutoReply    = true;
    protected bool $isBlocked           = true;
    protected array $arrReport          = [];


    public function __construct(protected MailerInterface $mailer, protected ProjectDir $projectDir, protected array $arrConfig = [])
    {
        $this->init();
    }


    public function init() : static
    {
        $this->email    = new TemplatedEmail();

        $senderName     = $this->arrConfig["from"]["name"] ?? null;
        $senderAddress  = $this->arrConfig["from"]["address"] ?? null;

        if( !empty($senderAddress) ) {
            $this->email->from( new Address($senderAddress, $senderName) );
        }

        $toName     = $this->arrConfig["to"]["name"] ?? null;
        $toAddress  = $this->arrConfig["to"]["address"] ?? null;

        if( !empty($toAddress) ) {
            $this->email->to( new Address($toAddress, $toName) );
        }

        return $this;
    }


    public function block(bool $bool) : static
    {
        $this->isBlocked = $bool;
        return $this;
    }


    public function setDisableAutoReply(bool $disable = true) : static
    {
        $this->disableAutoReply = $disable;
        return $this;
    }


    protected function build(string $subjectUnprefixed, string $templateName, ?array $arrTemplateData = [], null|string|array $to = null) : static
    {
        if( empty($to) ) {

            // use default recipients from arrConfig

        } else {

            $this->email->getHeaders()->remove('To');
            $this->addRecipients($to, 'to');
        }

        // subject
        $subjectPrefix  = $this->arrConfig["subject"]["tag"] ?? '';
        $subject        = empty($subjectPrefix) ? $subjectUnprefixed : ($subjectPrefix . " " . $subjectUnprefixed);
        $this->email->subject($subject);

        //
        if( $this->disableAutoReply ) {

            $headers = $this->email->getHeaders();
            $headers->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
        }

        $arrTemplateParams = [
            "From"      => $this->email->getFrom()[0] ?? null,
            "To"        => $this->email->getTo(),
            "ToFirst"   => $this->email->getTo()[0] ?? null,
            "date"      => date('Y-m-d H:i:s'),
        ];

        /**
         * 💡 siteUrl is not needed!
         * .env: APP_SITE_DOMAIN=www.example.com
         * services.yaml:
         *   parameters:
         *     ## Make the domain and site URL available to CLI Commands
         *     router.request_context.host: '%env(APP_SITE_DOMAIN)%'
         *     router.request_context.scheme: 'https'
         */

        // body
        $this->email
            ->htmlTemplate($templateName)
            ->context( array_merge($arrTemplateData, $arrTemplateParams) );

        return $this;
    }


    public function addTo(null|string|array $recipients) : static { return $this->addRecipients($recipients, 'to'); }

    public function addBcc(null|string|array $recipients) : static { return $this->addRecipients($recipients, 'bcc'); }


    protected function addRecipients(null|string|array $recipients, string $fieldName) : static
    {
        if( empty($recipients) ) {
            return $this;
        }

        if( is_string($recipients) ) {
            $recipients = [$recipients];
        }

        foreach($recipients as $item) {

            if( empty($item) ) {
                continue;
            }

            if( is_array($item) ) {

                $toName     = $item["name"] ?? null;
                $toAddress  = $item["address"] ?? null;
                $toAddress  = trim($toAddress);

                if( empty($toAddress) ) {
                    continue;
                }

                $toAddress = new Address($toAddress, $toName);

            } else {

                $toAddress = trim($item);

                if( empty($toAddress) ) {
                    continue;
                }

                $toAddress = new Address($toAddress);
            }

            $fxCheck = 'get' . ucfirst($fieldName);
            if( empty($this->email->$fxCheck()) ) {

                $this->email->$fieldName($toAddress);

            } else {

                $fxAdd = 'add' . ucfirst($fieldName);
                $this->email->$fxAdd($toAddress);
            }
        }

        return $this;
    }


    public function attachFromVar(string $fileSubpath, ?string $fileNameToShow = null) : static
    {
        $fullFilePath  = $this->projectDir->getVarDirFromFilePath($fileSubpath);
        $fullFilePath .= $fileSubpath;
        return $this->attach($fullFilePath, $fileNameToShow);
    }


    public function attach(string $filepath, ?string $fileNameToShow = null) : static
    {
        $this->email->addPart( new DataPart(new File($filepath), $fileNameToShow) );
        return $this;
    }


    public function addUnsubscribeHeader(?string $unsubscribeUrl, ?string $unsubscribeMailTo) : static
    {
        // 📚 https://datatracker.ietf.org/doc/html/rfc8058#section-8.1

        $arrListUnsubscribeValues = [];
        if( !empty($unsubscribeMailTo) ) {
            $arrListUnsubscribeValues[] = '<mailto:' . $unsubscribeMailTo . '>';
        }

        if( !empty($unsubscribeUrl) ) {
            $arrListUnsubscribeValues[] = '<' . $unsubscribeUrl . '>';
        }

        if( empty($arrListUnsubscribeValues) ) {
            return $this;
        }

        $headers = $this->email->getHeaders();

        $headers->addTextHeader('List-Unsubscribe', implode(', ', $arrListUnsubscribeValues) );

        if( !empty($unsubscribeUrl) ) {
            $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        }

        return $this;
    }


    public function send() : void
    {
        $arrRecipients = $this->email->getTo();

        if( $this->isBlocked ) {

            $this->addReportEntry($arrRecipients, false, 'isBlocked');
            return;
        }

        try {
            $this->mailer->send($this->email);
            $this->addReportEntry($arrRecipients, true);

        } catch (TransportExceptionInterface $ex) {

            $this->addReportEntry($arrRecipients, false, $ex->getMessage());
            throw $ex;
        }
    }


    public function getEmail() : TemplatedEmail
    {
        /**
         * 💡
         * $templatePath   = $mailer->getEmail()->getHtmlTemplate();
         * $arrParam       = $mailer->getEmail()->getContext();
         */
        return $this->email;
    }


    protected function addReportEntry(array $arrRecipients, bool $success, ?string $message = null) : void
    {
        $arrRecipientsAsString = [];
        foreach($arrRecipients as $address) {
            $arrRecipientsAsString[] = $address->getName() . " <" . $address->getAddress() . ">";
        }

        $this->arrReport[] = [
            "recipients"    => implode(', ', $arrRecipientsAsString),
            "success"       => $success,
            "message"       => $message
        ];
    }


    public function getFailingReport() : array
    {
        $arrResult = array_filter($this->arrReport, fn($row) => $row["success"] == false);
        foreach($arrResult as &$row) {
            unset($row['success']);
        }
        return $arrResult;
    }
}
