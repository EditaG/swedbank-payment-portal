<?php

namespace SwedbankPaymentPortal\PayPal\Type;

use JMS\Serializer\Annotation;
use JMS\Serializer\Context;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use SwedbankPaymentPortal\SharedEntity\Type\AbstractEnumerableType;

/**
 * Acknowledgement status.
 */
class AcknowledgmentStatus extends AbstractEnumerableType
{
    /**
     * Acknowledged.
     *
     * @return AcknowledgmentStatus
     */
    final public static function success()
    {
        return self::get('Success');
    }

    /**
     * Acknowledged with warning.
     *
     * @return AcknowledgmentStatus
     */
    final public static function successWithWarning()
    {
        return self::get('SuccessWithWarning');
    }

    /**
     * Failed.
     *
     * @return AcknowledgmentStatus
     */
    final public static function failure()
    {
        return self::get('Failure');
    }

    /**
     * Failed with warning.
     *
     * @return AcknowledgmentStatus
     */
    final public static function failureWithWarning()
    {
        return self::get('FailureWithWarning');
    }

    /**
     * Warning.
     *
     * @return AcknowledgmentStatus
     */
    final public static function warning()
    {
        return self::get('Warning');
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
