<?php

namespace SwedbankPaymentPortal\BankLink\CommunicationEntity\NotificationQuery;

use JMS\Serializer\Annotation;
use JMS\Serializer\XmlSerializationVisitor;

/**
 * A response to the notification from the system.
 *
 * @Annotation\XmlRoot("Response")
 */
class MerchantNotificationResponse
{
    /**
     * What text is returned.
     *
     * @var string
     *
     * @Annotation\Type("string")
     */
    private $responseString = 'OK';

    /**
     * ResponseString getter.
     *
     * @return int
     */
    public function getResponseString()
    {
        return $this->responseString;
    }

    /**
     * ResponseString setter.
     *
     * @param int $responseString
     */
    public function setResponseString($responseString)
    {
        $this->responseString = $responseString;
    }

    /**
     * Custom serialization logic.
     *
     *
     *
     * @param XmlSerializationVisitor $visitor
     */
    public function serialize(XmlSerializationVisitor $visitor)
    {
        $document = $visitor->getDocument();
        $currentNode = $document->createElement('Response');
        $currentNode->nodeValue = $this->getResponseString();
        $document->appendChild($currentNode);
    }
}
