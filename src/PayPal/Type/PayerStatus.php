<?php

namespace SwedbankPaymentPortal\PayPal\Type;

use JMS\Serializer\Annotation;
use JMS\Serializer\Context;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use SwedbankPaymentPortal\SharedEntity\Type\AbstractEnumerableType;

/**
 * Payer status.
 */
class PayerStatus extends AbstractEnumerableType
{
    /**
     * Verified.
     *
     * @return PayerStatus
     */
    final public static function verified()
    {
        return self::get('verified');
    }

    /**
     * Unverified.
     *
     * @return PayerStatus
     */
    final public static function unverified()
    {
        return self::get('unverified');
    }

    /**
     * Custom deserialization logic.
     *
     *
     *
     * @param XmlDeserializationVisitor $visitor
     * @param null|array                $data
     * @param Context                   $context
     */
    public function deserialize(XmlDeserializationVisitor $visitor, $data, Context $context)
    {
        $this->assignId((string)$data);
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
        $visitor->getCurrentNode()->nodeValue = $this->id();
    }
}
