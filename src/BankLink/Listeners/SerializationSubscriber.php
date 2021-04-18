<?php

namespace SwedbankPaymentPortal\BankLink\Listeners;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PaymentAttemptResponse\QueryTxnResult\Method;
use SwedbankPaymentPortal\SerializeHandlerCallbackInterface;

/**
 * Handles serialization and deserialization.
 */
class SerializationSubscriber implements EventSubscriberInterface
{
    /**
     * Extra data in the object.
     *
     * @var array
     */
    private $extraData = [];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ['event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'],
            ['event' => 'serializer.post_deserialize', 'method' => 'onPostDeserialize'],
            ['event' => 'serializer.pre_deserialize', 'method' => 'onPreDeserialize'],
        ];
    }

    /**
     * Collects missing data for items which isn't strictly defined.
     *
     * @param PreDeserializeEvent $event
     */
    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $type = $event->getType();
        $this->extraData[$type['name']] = [];
        if ($type['name'] == Method::class) {
            foreach ((array)$event->getData() as $key => $value) {
                if (substr($key, 0, 3) === 'VK_') {
                    $this->extraData[$type['name']][$key] = $value;
                }
            }
        }
    }

    /**
     * Adds missing data to the object which isn't strictly defined.
     *
     * @param ObjectEvent $event
     */
    public function onPostDeserialize(ObjectEvent $event)
    {
        $object = $event->getObject();
        if ($object instanceof Method) {
            /** @var XmlDeserializationVisitor $visitor */
            $object->setMethodSpecificData($this->extraData[Method::class]);
        }
    }

    /**
     * Adds missing data to the xml which isn't strictly defined.
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $object = $event->getObject();

        /** @var XmlSerializationVisitor $visitor */
        $visitor = $event->getContext()->getVisitor();
        if ($object instanceof Method) {
            $extraData = $object->getMethodSpecificData();
            /** @var \DOMElement $node */
            $node = $visitor->getCurrentNode();
            $dummyNode = $node->getElementsByTagName('__MethodSpecificData')->item(0);
            if ($dummyNode === null) {
                return;
            }
            $node->removeChild($dummyNode);
            foreach ($extraData as $key => $item) {
                 $node->appendChild(new \DOMElement($key, $item));
            }
        }
    }
}
