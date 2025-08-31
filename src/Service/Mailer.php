<?php
namespace TurboLabIt\BaseCommand\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class Mailer
{
    const DSN_LOCAL_SMTP = 'smtp://localhost?verify_peer=false';

    protected TemplatedEmail $email;
    protected bool $disableAutoReply    = true;
    protected bool $isBlocked           = true;
    protected array $arrReport          = [];


    public function __construct(
        protected MailerInterface $mailer, protected ProjectDir $projectDir,
        protected ParameterBagInterface $parameters, protected array $arrConfig = []
    )
        { $this->init(); }


    public function init() : static
    {
        $this->email = new TemplatedEmail();

        $this->setFrom();

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

        $this->addDisableAutoreplyHeaders();

        $subject = $this->buildSubject($subjectUnprefixed);
        $this->email->subject($subject);

        $arrTemplateParams = [
            "From"      => $this->email->getFrom()[0] ?? null,
            "To"        => $this->getTo(),
            "ToFirst"   => $this->getTo()[0] ?? null,
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

        $this->email
            ->htmlTemplate($templateName)
            ->context( array_merge($arrTemplateData, $arrTemplateParams) );

        return $this;
    }


    protected function addDisableAutoreplyHeaders() : static
    {
        if( !$this->disableAutoReply ) {
            return $this;
        }

        $headers = $this->email->getHeaders();

        $arrDisableAutoreplyHeaders = [
            'X-Auto-Response-Suppress'  => 'OOF, DR, RN, NRN, AutoReply',
            'Auto-Submitted'            => 'auto-generated'
        ];

        foreach($arrDisableAutoreplyHeaders as $header => $value) {

            $headers->remove($header);
            $headers->addTextHeader($header, $value);
        }

        return $this;
    }


    protected function buildSubject(string $subjectUnprefixed) : string
    {
        $fullSubject = $this->getEnvTag();

        $subjectPrefix  = $this->arrConfig["subject"]["tag"] ?? '';
        $subjectPrefix  = trim($subjectPrefix);
        if( !empty($subjectPrefix) ) {
           $fullSubject .= " " . $subjectPrefix;
        }

        $fullSubject = trim($fullSubject) . " " . $subjectUnprefixed;
        $fullSubject = trim($fullSubject);
        $fullSubject = html_entity_decode($fullSubject, ENT_QUOTES, 'UTF-8');

        return $fullSubject;
    }


    protected function getEnvTag(bool $includeProd = false) : string
    {
        $env = $this->parameters->get("kernel.environment");

        if( $env == 'prod' && !$includeProd ) {
            return '';
        }

        return "[" . strtoupper($env) . "] ";
    }


    public function setFrom(?string $senderAddress = null, ?string $senderName = null) : static
    {
        if( empty($senderName) ) {
            $senderName = $this->arrConfig["from"]["name"] ?? null;
        }

        if( empty($senderAddress) ) {
            $senderAddress = $this->arrConfig["from"]["address"] ?? null;
        }

        if( !empty($senderAddress) ) {
            $this->email->from( new Address($senderAddress, $senderName) );
        }

        return $this;
    }


    public function addTo(null|string|array $recipients) : static { return $this->addRecipients($recipients, 'to'); }

    public function getTo() : array { return $this->email->getTo(); }

    public function addCc(null|string|array $recipients) : static { return $this->addRecipients($recipients, 'cc'); }

    public function getCc() : array { return $this->email->getCc(); }

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

            if( is_object($item) && method_exists($item, 'getUsername') && method_exists($item, 'getEmail') ) {

                $item = [
                    'name'      => $item->getUsername(),
                    'address'   => $item->getEmail()
                ];

            } elseif( is_object($item) && method_exists($item, 'getEmail') ) {

                $item = $item->getEmail();

            } elseif( is_object($item) ) {

                throw new \InvalidArgumentException(
                    "Invalid recipients element. Each item must be ['name', 'email'] or implement getUsername()+getEmail(). " .
                    get_class($item) . " doesn't"
                );
            }


            if( is_array($item) ) {

                $toName     = $item["name"] ?? '';
                $toName     = trim($toName);
                $toAddress  = $item["address"] ?? '';
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

        $headers->remove('List-Unsubscribe');
        $headers->addTextHeader('List-Unsubscribe', implode(', ', $arrListUnsubscribeValues) );

        if( !empty($unsubscribeUrl) ) {

            $headers->remove('List-Unsubscribe-Post');
            $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        }

        return $this;
    }


    public function useLocalSmtpOnce() : static
    {
        /**
         * Requires a configuration change to config/packages/mailer.yaml:
        framework:
            mailer:
                transports:
                    main: '%env(MAILER_DSN)%'
                    localsmtp: !php/const 'TurboLabIt\BaseCommand\Service\Mailer::DSN_LOCAL_SMTP'

        */

        return $this->switchTransport('localsmtp');
    }


    public function switchTransport(string $transportName) : static
    {
        // 📚 https://symfony.com/doc/current/mailer.html#multiple-email-transports
        $headers = $this->email->getHeaders();
        $headers->remove('X-Transport');
        $headers->addTextHeader('X-Transport', $transportName);
        return $this;
    }


    public function sendIfHasToRecipients() : bool
    {
        if( empty($this->getTo()) ) {
            return false;
        }

        $this->send();
        return true;
    }


    public function send() : void
    {
        $arrRecipients = $this->getTo();

        if( $this->isBlocked ) {

            $this->addReportEntry($arrRecipients, false, 'isBlocked');
            return;
        }

        try {
            $this->mailer->send($this->email);
            $this->addReportEntry($arrRecipients, true);

        } catch (\Exception $ex) {

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
