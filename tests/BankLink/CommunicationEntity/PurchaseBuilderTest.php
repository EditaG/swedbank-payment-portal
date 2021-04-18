<?php

namespace  Tests\BankLink\CommunicationEntity;

use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\Transaction\APMTxn\AlternativePayment\TransactionDetails\PersonalDetails;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\Transaction\APMTxn\AlternativePayment\TransactionDetails\BillingDetails;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\Transaction\APMTxn\AlternativePayment\TransactionDetails;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\Transaction\APMTxn\AlternativePayment\MethodDetails;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\Transaction\APMTxn\AlternativePayment;
use SwedbankPaymentPortal\SharedEntity\Transaction\TxnDetails;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\Transaction\APMTxn;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\Transaction\HPSTxn;
use SwedbankPaymentPortal\SharedEntity\Authentication;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\Transaction;
use SwedbankPaymentPortal\BankLink\PurchaseBuilder;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\Type\PaymentMethod;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\Type\ServiceType;
use SwedbankPaymentPortal\BankLink\CommunicationEntity\PurchaseRequest\PurchaseRequest;
use Tests\AbstractCommunicationEntityTest;

/**
 * Checks if stuff is serialized as expected.
 */
class PurchaseBuilderTest extends AbstractCommunicationEntityTest
{
    /**
     * Test with purchase object.
     */
    public function testBuilder()
    {
        $billingDetails = new BillingDetails(new BillingDetails\AmountDetails(100, 2, 978));
        $personalDetails = new PersonalDetails('test@example.com');

        $transactionDetails = new TransactionDetails(
            'description',
            'https://www.redirect.com/TestSite/Success.aspx',
            'https://www.redirect.com/TestSite/Failure.aspx',
            'en',
            $personalDetails,
            $billingDetails
        );
        $methodDetails = new MethodDetails(ServiceType::litBank());
        $alternativePayment = new AlternativePayment($transactionDetails, $methodDetails);

        $apmTransaction = new APMTxn(PaymentMethod::swedbank(), $alternativePayment);
        $hpsTransaction = new HPSTxn();
        $txnDetails = new TxnDetails('ordernumber/01');

        $transaction = new Transaction($txnDetails, $hpsTransaction, $apmTransaction);
        $authentication = new Authentication(123, '********');
        $expectedPurchase = new PurchaseRequest($transaction, $authentication);

        $purchaseBuilder = new PurchaseBuilder();
        $purchaseBuilder->setAmountValue(100)
            ->setDescription('description')
            ->setAmountExponent(2)
            ->setAmountCurrencyCode(978)
            ->setConsumerEmail('test@example.com')
            ->setServiceType(ServiceType::litBank())
            ->setPaymentMethod(PaymentMethod::swedbank())
            ->setSuccessUrl('https://www.redirect.com/TestSite/Success.aspx')
            ->setFailureUrl('https://www.redirect.com/TestSite/Failure.aspx')
            ->setMerchantReference('ordernumber/01')
            ->setClientId(123)
            ->setPassword('********')
            ->setLanguage('en');

        $this->assertEquals($expectedPurchase, $purchaseBuilder->getPurchaseRequest());
    }

    /**
     * Test builder with setting default value.
     */
    public function testBuilderOverrideDefault()
    {
        $billingDetails = new BillingDetails(new BillingDetails\AmountDetails(100, 2, 978));
        $personalDetails = new PersonalDetails('test@example.com');

        $transactionDetails = new TransactionDetails(
            'description',
            'https://www.redirect.com/TestSite/Success.aspx',
            'https://www.redirect.com/TestSite/Failure.aspx',
            'en',
            $personalDetails,
            $billingDetails
        );
        $methodDetails = new MethodDetails(ServiceType::litBank());
        $alternativePayment = new AlternativePayment($transactionDetails, $methodDetails);

        $apmTransaction = new APMTxn(PaymentMethod::swedbank(), $alternativePayment);
        $hpsTransaction = new HPSTxn(5);
        $txnDetails = new TxnDetails('ordernumber/01');

        $transaction = new Transaction($txnDetails, $hpsTransaction, $apmTransaction);
        $authentication = new Authentication(123, '********');
        $expectedPurchase = new PurchaseRequest($transaction, $authentication);

        $purchaseBuilder = new PurchaseBuilder();
        $purchaseBuilder->setAmountValue(100)
            ->setDescription('description')
            ->setAmountExponent(2)
            ->setAmountCurrencyCode(978)
            ->setConsumerEmail('test@example.com')
            ->setServiceType(ServiceType::litBank())
            ->setPaymentMethod(PaymentMethod::swedbank())
            ->setSuccessUrl('https://www.redirect.com/TestSite/Success.aspx')
            ->setFailureUrl('https://www.redirect.com/TestSite/Failure.aspx')
            ->setMerchantReference('ordernumber/01')
            ->setClientId(123)
            ->setPassword('********')
            ->setLanguage('en')
            ->setPageSetId(5);

        $this->assertEquals($expectedPurchase, $purchaseBuilder->getPurchaseRequest());
    }

    /**
     * Check what happens when required value is not set (should throw an exception).
     */
    public function testBuilderNoValueSet()
    {
        $this->expectExceptionMessage("Property named 'failureUrl' isn't set. Please set it.");
        $this->expectException(\InvalidArgumentException::class);
        $purchaseBuilder = new PurchaseBuilder();
        $purchaseBuilder->setAmountValue(100)
            ->setAmountExponent(2)
            ->setAmountCurrencyCode(978)
            ->setConsumerEmail('test@example.com')
            ->setServiceType(ServiceType::litBank())
            ->setPaymentMethod(PaymentMethod::swedbank())
            ->setSuccessUrl('https://www.redirect.com/TestSite/Success.aspx')
            ->setMerchantReference('ordernumber/01')
            ->setClientId(123)
            ->setPassword('********')
            ->setLanguage('en')
            ->setPageSetId(5)
            ->setDescription('description')
            ->getPurchaseRequest();
    }

    /**
     * Check what happens when required value is not set (should throw an exception).
     */
    public function testGivenMerchantReferenceWasGreaterThan16Chars()
    {
        $this->expectExceptionMessage("merchantReference must be max 16 characters. Given: 12345678901234567, chars: 17");
        $this->expectException(\RuntimeException::class);
        $purchaseBuilder = new PurchaseBuilder();
        $purchaseBuilder->setAmountValue(100)
            ->setDescription('description')
            ->setAmountExponent(2)
            ->setAmountCurrencyCode(978)
            ->setConsumerEmail('test@example.com')
            ->setServiceType(ServiceType::litBank())
            ->setPaymentMethod(PaymentMethod::swedbank())
            ->setSuccessUrl('https://www.redirect.com/TestSite/Success.aspx')
            ->setFailureUrl('https://www.redirect.com/TestSite/Failure.aspx')
            ->setMerchantReference('12345678901234567')
            ->setClientId(123)
            ->setPassword('********')
            ->setLanguage('en')
            ->setPageSetId(5)
            ->getPurchaseRequest();
    }
}
