<?php

namespace SwedbankPaymentPortal\SharedEntity;

use JMS\Serializer\Annotation;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\Context;

/**
 * The amount debited from the consumer’s account at Swedbank.
 *
 * The field type is a numeric value in cents.
 */
class Amount
{
    /**
     * The transaction amount.
     *
     * @var float
     *
     * @Annotation\Type("float")
     */
    private $value;

    /**
     * The transaction currency.
     *
     * @var string
     *
     * @Annotation\XmlAttribute
     * @Annotation\SerializedName("currency")
     * @Annotation\Type("string")
     * @Annotation\AccessType("reflection")
     */
    private $currency = 'EUR';

    /**
     * Amount constructor.
     *
     * @param float $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Value getter.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Value setter.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Custom deserialization logic.
     *
     *
     *
     * @param XmlDeserializationVisitor $visitor
     * @param \SimpleXMLElement         $data
     * @param Context                   $context
     */
    public function deserialize(XmlDeserializationVisitor $visitor, $data, Context $context)
    {
        $this->currency = (string)$this->getAttribute($data->attributes(), 'currency');
        $this->value = (string)$data;
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
        /** @var \DOMElement $node */
        $node = $visitor->getCurrentNode();
        $node->setAttribute('currency', $this->currency);
        $node->nodeValue = $this->value;
    }

    /**
     * Returns attribute.
     *
     * @param \SimpleXMLElement $attributes
     * @param string            $key
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    private function getAttribute($attributes, $key)
    {
        if (!isset($attributes[$key])) {
            throw new \InvalidArgumentException("Attribute {$key} not set.");
        }

        return $attributes[$key];
    }
}
