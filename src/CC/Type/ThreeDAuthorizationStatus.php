<?php

namespace SwedbankPaymentPortal\CC\Type;

use JMS\Serializer\Annotation;
use JMS\Serializer\Context;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use SwedbankPaymentPortal\SharedEntity\Type\AbstractEnumerableType;
use SwedbankPaymentPortal\SharedEntity\Type\AbstractStatus;

/**
 * Any other return code should be treated as a declined / failed payment.
 *
 * This is the ultimate field that should be used to determine the status of the transaction.
 */
class ThreeDAuthorizationStatus extends AbstractStatus
{
    /**
     * Accepted.
     *
     * @return ThreeDAuthorizationStatus
     */
    final public static function accepted()
    {
        return self::get(1);
    }

    /**
     * Declined.
     *
     * @return ThreeDAuthorizationStatus
     */
    final public static function secureAuthenticationRequired()
    {
        return self::get(7);
    }

    /**
     * 3DS Invalid reference
     *
     * @return ThreeDAuthorizationStatus
     */
    final public static function invalid3DSReference()
    {
        return self::get(167);
    }

    /**
     * 3DS txn cannot be authorized
     * @return $this
     */
    final public static function auth3DSTransactionCannotBeAuthorized()
    {
        return self::get(168);
    }

    /**
     * 3DS invalid pares
     * @return $this
     */
    final public static function invalid3DSPares()
    {
        return self::get(176);
    }

    /**
     * 3DS Payer failed Verification
     * @return $this
     */
    final public static function payerFailed3DSVerification()
    {
        return self::get(179);
    }

    /**
     * Unable to authenticate
     * @return $this
     */
    final public static function unableToAuthenticate()
    {
        return self::get(188);
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
        $this->assignId((int)$data);
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
