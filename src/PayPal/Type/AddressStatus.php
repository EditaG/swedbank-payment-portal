<?php

namespace SwedbankPaymentPortal\PayPal\Type;

use JMS\Serializer\Annotation;
use JMS\Serializer\Context;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use SwedbankPaymentPortal\SharedEntity\Type\AbstractEnumerableType;

/**
 * Address status.
 */
class AddressStatus extends AbstractEnumerableType
{
    /**
     * Confirmed.
     *
     * @return AddressStatus
     */
    final public static function confirmed()
    {
        return self::get('Confirmed');
    }

    /**
     * No status.
     *
     * @return AddressStatus
     */
    final public static function none()
    {
        return self::get('None');
    }

    /**
     * Unconfirmed.
     *
     * @return AddressStatus
     */
    final public static function unconfirmed()
    {
        return self::get('Unconfirmed');
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
