<?php

namespace SwedbankPaymentPortal;

use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\DateHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer as JMSSerializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\NotificationQuery\MerchantNotificationResponse;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PaymentAttemptResponse\QueryTxnResult\Amount;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\Type\PaymentMethod;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\Type\ServiceType;
use SwedbankPaymentPortal\CC\HCCCommunicationEntity\AuthorizationRequest\Transaction\CardDetails;
use SwedbankPaymentPortal\CC\Type\AuthorizationStatus;
use SwedbankPaymentPortal\CC\Type\CardholderRegisterStatus;
use SwedbankPaymentPortal\CC\Type\ScreeningAction;
use SwedbankPaymentPortal\CC\Type\ThreeDAuthorizationStatus;
use SwedbankPaymentPortal\CC\Type\TransactionChannel;
use SwedbankPaymentPortal\PayPal\Type\AcknowledgmentStatus;
use SwedbankPaymentPortal\PayPal\Type\AddressStatus as AddressStatus;
use SwedbankPaymentPortal\PayPal\Type\PayerStatus;
use SwedbankPaymentPortal\PayPal\Type\PaymentStatus;
use SwedbankPaymentPortal\PayPal\Type\PayPalBool;
use SwedbankPaymentPortal\SharedEntity\AbstractResponse;
use SwedbankPaymentPortal\SharedEntity\Type\MerchantMode;
use SwedbankPaymentPortal\SharedEntity\Type\PurchaseStatus;
use SwedbankPaymentPortal\SharedEntity\Type\ResponseStatus;

/**
 * Turns class into xml string.
 */
class Serializer
{
    /**
     * @var JMSSerializer
     */
    private $serializer;

    /**
     * @var EventSubscriberInterface[]
     */
    private $subscribers = [];

    /**
     * Return xml data.
     *
     * @param object $object
     *
     * @return string
     */
    public function getXml($object)
    {
        return $this->getSerializer()->serialize($object, 'xml');
    }

    /**
     * Return xml data.
     *
     * @param string $xmlData
     * @param string $objectClass
     *
     * @return AbstractResponse
     */
    public function getObject($xmlData, $objectClass)
    {
        return $this->getSerializer()->deserialize($xmlData, $objectClass, 'xml');
    }

    /**
     * Adds subscriber.
     *
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        if ($this->serializer) {
            throw new \InvalidArgumentException('Serializer already created, cannot add subscriber.');
        }

        $this->subscribers[] = $subscriber;
    }

    /**
     * Returns serializer.
     *
     * @return JMSSerializer
     */
    private function getSerializer()
    {
        if (!$this->serializer) {
            $this->serializer = SerializerBuilder::create()->configureListeners(
                function (EventDispatcher $dispatcher) {
                    foreach ($this->subscribers as $subscriber) {
                        $dispatcher->addSubscriber($subscriber);
                    }
                }
            )
            ->configureHandlers(function(HandlerRegistry $registry) {
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        ResponseStatus::class,
                        'xml',
                    function($visitor, $data, $type, Context $context) {
                        return $data->serialize($visitor, $data, $type, $context);
                    }
                );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        ResponseStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            if (array_key_exists((string)$data, ResponseStatus::TYPE_MAP)) {
                                return ResponseStatus::fromId(ResponseStatus::TYPE_MAP[(string)$data]);
                            } else {
                                return ResponseStatus::fromId((int)$data);
                            }
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        MerchantMode::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );

                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        MerchantMode::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return MerchantMode::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        PurchaseStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );

                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        PurchaseStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return PurchaseStatus::fromId((int)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        MerchantNotificationResponse::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        Amount::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            $amount = new Amount(null, null, null);
                            $amount->deserialize($visitor, $data, $context);

                            return $amount;
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        Amount::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        PaymentMethod::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return PaymentMethod::fromId((string) $data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        PaymentMethod::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        ServiceType::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return ServiceType::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        ServiceType::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        ScreeningAction::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return ScreeningAction::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        ScreeningAction::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        TransactionChannel::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return TransactionChannel::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        TransactionChannel::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        AcknowledgmentStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return AcknowledgmentStatus::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        AcknowledgmentStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        PayPalBool::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return PayPalBool::fromId((int)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        PayPalBool::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        SharedEntity\Amount::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            $amount = new SharedEntity\Amount(null);
                            $amount->deserialize($visitor, $data, $context);

                            return $amount;
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        SharedEntity\Amount::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        AddressStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return AddressStatus::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        AddressStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        PayerStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return PayerStatus::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        PayerStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        PaymentStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return PaymentStatus::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        PaymentStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        CC\HPSCommunicationEntity\HPSQueryResponse\ResponseStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            if (array_key_exists((string)$data, CC\HPSCommunicationEntity\HPSQueryResponse\ResponseStatus::TYPE_MAP)) {
                                return CC\HPSCommunicationEntity\HPSQueryResponse\ResponseStatus::fromId(CC\HPSCommunicationEntity\HPSQueryResponse\ResponseStatus::TYPE_MAP[(string)$data]);
                            } else {
                                return CC\HPSCommunicationEntity\HPSQueryResponse\ResponseStatus::fromId((int)$data);
                            }
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        CC\HPSCommunicationEntity\HPSQueryResponse\ResponseStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor, $data, $type, $context);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        CardholderRegisterStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return CardholderRegisterStatus::fromId((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        CardholderRegisterStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        ThreeDAuthorizationStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return ThreeDAuthorizationStatus::fromId((int)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        ThreeDAuthorizationStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        AuthorizationStatus::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return AuthorizationStatus::fromId((int)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        AuthorizationStatus::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        CardDetails::class,
                        'xml',
                        function(DeserializationVisitorInterface $visitor, \SimpleXMLElement $data, $type, Context $context) {
                            return new CardDetails((string)$data);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        CardDetails::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            return $data->serialize($visitor);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                        \DateTime::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            $dateTimeHandler = new DateHandler();

                            return $dateTimeHandler->deserializeDateTimeFromXml($visitor, $data, $type);
                        }
                    );
                $registry
                    ->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        \DateTime::class,
                        'xml',
                        function($visitor, $data, $type, Context $context) {
                            $dateTimeHandler = new DateHandler();

                            return $dateTimeHandler->serializeDateTime($visitor, $data, $type, $context);
                        }
                    );
            })
            ->build();
        }

        return $this->serializer;
    }
}
