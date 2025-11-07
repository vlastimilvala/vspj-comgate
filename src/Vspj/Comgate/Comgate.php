<?php declare(strict_types = 1);

namespace Vspj\PlatebniBrana\Comgate;

use Vspj\PlatebniBrana\Comgate\Base\ComgateBase;
use Vspj\PlatebniBrana\Comgate\Base\ComgatePlatba;
use Vspj\PlatebniBrana\Comgate\Base\ComgatePlatbaStav;
use Vspj\PlatebniBrana\Comgate\Base\ComgateReturnRoute;
use Vspj\PlatebniBrana\Comgate\Exception\ComgateException;
use Comgate\SDK\Entity\Codes\CategoryCode;
use Comgate\SDK\Entity\Codes\CurrencyCode;
use Comgate\SDK\Entity\Codes\DeliveryCode;
use Comgate\SDK\Entity\Codes\PaymentStatusCode;
use Comgate\SDK\Entity\Codes\RequestCode;
use Comgate\SDK\Entity\Money;
use Comgate\SDK\Entity\Payment;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class Comgate extends ComgateBase
{

	/**
	 * Vytvoření platebního požadavku na platební bránu. Dojde k přesměrování na platební bránu.
	 *
	 * @param ComgatePlatba $comgatePlatba Objekt vytvořené šablony platby
	 * @param ComgateReturnRoute $returnRoute Objekt návratové Symfony routy (při návratu zpět z platební brány)
	 */
	public function novaPlatba(ComgatePlatba $comgatePlatba, ComgateReturnRoute $returnRoute): RedirectResponse
	{
		$returnUrl = $this->generateReturnUrl($returnRoute);
		$payment = new Payment();
		$payment
			->setPrice(Money::ofFloat($comgatePlatba->getCastkaCzk()))
			->setCurrency(CurrencyCode::CZK)
			->setLabel($comgatePlatba->getPopisPlatby())
			->setReferenceId($comgatePlatba->getSpecifickySymbol())
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

	/**
	 * Ověření stavu platby po návratu z platební brány
	 * VŽDY	volat před voláním metody novaPlatba!
	 *
	 * @param Request $request Symfony HTTP pozadavek
	 * @return ComgatePlatbaStav|null
	 */
	public function overitStavPlatby(Request $request): ?ComgatePlatbaStav
	{
		$transactionId = $request->query->get(self::TRANSACTION_ID_ATRIBUT);
		$referenceId = $request->query->get(self::REFERENCE_ID_ATRIBUT);

		if (!isset($transactionId) || !isset($referenceId)) {
			return null;
		}

		$paymentStatusResponse = $this->client->getStatus($transactionId);

		switch ($paymentStatusResponse->getStatus()) {
			case PaymentStatusCode::PAID:
				return new ComgatePlatbaStav($transactionId, $referenceId, PaymentStatusCode::PAID, ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZAPLACENO);
			case PaymentStatusCode::CANCELLED:
				return new ComgatePlatbaStav($transactionId, $referenceId, PaymentStatusCode::CANCELLED, ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZRUSENO);
			case PaymentStatusCode::PENDING:
				return new ComgatePlatbaStav($transactionId, $referenceId, PaymentStatusCode::PENDING, ComgatePlatbaStav::COMGATE_PLATBA_STAV_CEKAJICI);
			case PaymentStatusCode::AUTHORIZED:
				return new ComgatePlatbaStav($transactionId, $referenceId, PaymentStatusCode::AUTHORIZED, ComgatePlatbaStav::COMGATE_PLATBA_STAV_AUTORIZOVANO);
			default:
				throw new ComgateException('Byl vrácen neznámý stav platby ID: ' . $transactionId . ', Ref. ID: ' . $referenceId);
		}
	}

	protected function generateReturnUrl(ComgateReturnRoute $returnRoute): string
	{
		return $this->urlGenerator->generate($returnRoute->getSymfonyRoute(), $returnRoute->getSymfonyRouteParameters(), UrlGeneratorInterface::ABSOLUTE_URL) .
			'?' . self::TRANSACTION_ID_ATRIBUT . '=${id}&' . self::REFERENCE_ID_ATRIBUT . '=${refId}';
	}

}
