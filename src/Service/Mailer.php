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
    protected bool $isBlocked = true;
    protected TemplatedEmail $email;
    protected array $arrReport = [];


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


    protected function build(string $subjectUnprefixed, string $templateName, ?array $arrTemplateData = [], null|string|array $to = null) : static
    {
        // to
        if( empty($to) ) {

            // use default recipients from arrConfig

        } elseif ( is_string($to) ) {

            $this->email->to(new Address($to));

        } elseif( !empty( array_column($to, "address") ) ) {

            // array of recipients: "address" => 'xxx', "name" => 'yyy')
            $firstToSet = false;
            $arrTo      = [];
            foreach($to as $recipient) {

                $toName     = $recipient["name"] ?? null;
                $toAddress  = $recipient["address"] ?? null;

                if( empty($toAddress) ) {
                    continue;
                }

                $toAddress = new Address($toAddress, $toName);

                if( !$firstToSet ) {

                    $this->email->to($toAddress);
                    $firstToSet = true;
                    continue;
                }

                $this->email->addTo($toAddress);
            }

        } else {

            // array of recipients: addr1, addr2, ..)
            $firstToSet = false;
            foreach($to as $toAddress) {

                if( empty($toAddress) ) {
                    continue;
                }

                $toAddress = new Address($toAddress);

                if( !$firstToSet ) {

                    $this->email->to($toAddress);
                    $firstToSet = true;
                    continue;
                }

                $this->email->addTo($toAddress);
            }
        }

        // subject
        $subjectPrefix  = $this->arrConfig["subject"]["tag"] ?? '';
        $subject        = empty($subjectPrefix) ? $subjectUnprefixed : ($subjectPrefix . " " . $subjectUnprefixed);
        $this->email->subject($subject);

        $arrTemplateParams = [
            "From"      => $this->email->getFrom()[0] ?? null,
            "To"        => $this->email->getTo(),
            "ToFirst"   => $this->email->getTo()[0] ?? null,
            "date"      => date('Y-m-d H:i:s')
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

        } catch (\\Exception $ex) {

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
