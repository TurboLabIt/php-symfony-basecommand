<?php
namespace TurboLabIt\BaseCommand\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use TurboLabIt\BaseCommand\Service\ProjectDir;


class Mailer
{
    protected bool $isBlocked = true;
    protected TemplatedEmail $email;


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
            $this->email->from(new Address($senderAddress), $senderName);
        }

        $toName     = $this->arrConfig["to"]["name"] ?? null;
        $toAddress  = $this->arrConfig["to"]["address"] ?? null;

        if( !empty($toAddress) ) {
            $this->email->to(new Address($toAddress), $toName);
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
        $subjectPrefix  = $this->arrMailerConfig["subject"]["tag"] ?? '';
        $subject        = empty($subjectPrefix) ? $subjectUnprefixed : ($subjectPrefix . " " . $subjectUnprefixed);
        $this->email->subject($subject);

        if( !empty($this->arrConfig["siteUrl"]) ) {

            $siteUrl = $this->arrConfig["siteUrl"];

        } else if( !empty($this->arrConfig["siteDomain"]) ) {

            $siteUrl = 'https://' . $this->arrConfig["siteDomain"];

        } else {

            $siteUrl = null;
        }

        // body
        $this->email
            ->htmlTemplate($templateName)
            ->context(array_merge($arrTemplateData, [
                "date"      => date('Y-m-d H:i:s'),
                "siteUrl"   => $siteUrl
            ]));

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


    public function send()
    {
        if( $this->isBlocked ) {
            return false;
        }

        $result = $this->mailer->send($this->email);
        return $result;
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
}
