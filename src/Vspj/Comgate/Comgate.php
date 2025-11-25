<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate;

use Vspj\PlatebniBrana\Comgate\Base\ComgateBase;
use Vspj\PlatebniBrana\Comgate\Base\ComgatePlatba;
use Vspj\PlatebniBrana\Comgate\Base\ComgatePlatbaStav;
use Vspj\PlatebniBrana\Comgate\Base\ComgateReturnRoute;
use Vspj\PlatebniBrana\Comgate\Exception\ComgateException;
use Comgate\SDK\Entity\Codes\CategoryCode;
use Comgate\SDK\Entity\Codes\CurrencyCode;
use Comgate\SDK\Entity\Codes\DeliveryCode;
use Comgate\SDK\Entity\Codes\RequestCode;
use Comgate\SDK\Entity\Money;
use Comgate\SDK\Entity\Payment;
use Comgate\SDK\Exception\Api\PaymentNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class Comgate extends ComgateBase
{
    /**
     * Vytvoření platebního požadavku na platební bránu. Dojde k přesměrování na platební bránu.
     *
     * @param ComgatePlatba $comgatePlatba Objekt vytvořené šablony platby
     * @param ComgateReturnRoute $returnRoute Objekt návratové Symfony routy (při návratu zpět z platební brány)
     * @throws ComgateException
     */
    public function novaPlatba(ComgatePlatba $comgatePlatba, ComgateReturnRoute $returnRoute): RedirectResponse
    {
        $symboly = $comgatePlatba->getSpecifickySymbol() .
            self::SYMBOL_DELIMITER . $comgatePlatba->getVariabilniSymbol();
        $returnUrl = $this->generateReturnUrl($returnRoute, $comgatePlatba->getSpecifickySymbol(), $symboly);
        $payment = new Payment();
        $payment
            ->setPrice(Money::ofFloat($comgatePlatba->getCastkaCzk()))
            ->setCurrency(CurrencyCode::CZK)
            ->setLabel($comgatePlatba->getPopisPlatby())
            ->setReferenceId($comgatePlatba->getSpecifickySymbol()) //vala04 - refId znamená ID zákazníka (např. SS)
            ->setName($comgatePlatba->getVariabilniSymbol()) //vala04 - API parametr name znamená ID produktu/služby
            ->setFullName($comgatePlatba->getCeleJmenoPlatce())
            ->setEmail($comgatePlatba->getEmailPlatce())
            ->addMethod(self::COMGATE_METHODS)
            ->setCategory(CategoryCode::OTHER)
            ->setDelivery(DeliveryCode::ELECTRONIC_DELIVERY)
            ->setInitRecurring(false)
            ->setTest($this->testMode)
            ->setUrlPaid($returnUrl)
            ->setUrlPaidRedirect($returnUrl)
            ->setUrlCancelled($returnUrl)
            ->setUrlCancelledRedirect($returnUrl)
            ->setUrlPending($returnUrl)
            ->setUrlPendingRedirect($returnUrl);

        $createPaymentResponse = $this->client->createPayment($payment);
        if ($createPaymentResponse->getCode() !== RequestCode::OK) {
            throw new ComgateException($createPaymentResponse->getMessage());
        }

        return new RedirectResponse($createPaymentResponse->getRedirect());
    }

    public function jeNavratovyPozadavek(Request $request): bool
    {
        $transactionId = $request->query->get(self::TRANSACTION_ID_ATRIBUT);
        $referenceId = $request->query->get(self::REFERENCE_ID_ATRIBUT);
        $returningHash = $request->query->get(self::HASH_ATRIBUT);

        if (!isset($transactionId) || !isset($referenceId) || !isset($returningHash)) {
            return false;
        }

        return true;
    }

    /**
     * Ověření stavu platby po návratu z platební brány
     * VŽDY volat před voláním metody novaPlatba!
     *
     * @param Request $request Symfony HTTP pozadavek
     * @return ComgatePlatbaStav
     * @throws ComgateException
     */
    public function overitStavPlatbyPodleRequestu(Request $request): ComgatePlatbaStav
    {
        $transactionId = $request->query->get(self::TRANSACTION_ID_ATRIBUT);
        $transactionCode = $request->query->get(self::TRANSACTION_CODE_ATRIBUT);
        $referenceId = $request->query->get(self::REFERENCE_ID_ATRIBUT);
        $returningHash = $request->query->get(self::HASH_ATRIBUT);

        if (!$this->jeNavratovyPozadavek($request)) {
            throw new ComgateException('Neplatný požadavek při návratu z brány. ID: ' .
                $transactionId . ', Ref. ID: ' . $referenceId);
        }

        if ($this->hashKontrola($referenceId, $transactionCode, $returningHash) === null) {
            throw new ComgateException('Neplatný hash požadavek při návratu z brány. ID: ' .
                $transactionId . ', Ref. ID: ' . $referenceId);
        }

        try {
            $paymentStatusResponse = $this->client->getStatus($transactionId);
        } catch (PaymentNotFoundException $e) {
            throw new ComgateException('Neexistující ID platby. ID platby: ' .
                $transactionId . ', Ref. ID: ' . $referenceId);
        }

        return $this->overitStavPlatby($paymentStatusResponse);
    }

    /**
     * Ověření stavu již existující (na bráně založené) platby
     *
     * @param string $transactionId #ID transakce na straně platební brány
     * @return ComgatePlatbaStav
     * @throws ComgateException
     */
    public function overitStavPlatbyPodleTransakce(string $transactionId): ComgatePlatbaStav
    {
        $paymentStatusResponse = $this->client->getStatus($transactionId);

        return $this->overitStavPlatby($paymentStatusResponse);
    }
}
