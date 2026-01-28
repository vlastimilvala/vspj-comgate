<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate\Base;

use Comgate\SDK\Entity\Response\PaymentCreateResponse;
use Vspj\PlatebniBrana\Comgate\Exception\ComgateException;
use Vspj\PlatebniBrana\Comgate\Helper\ComgatePlaceholder;
use Comgate\SDK\Client;
use Comgate\SDK\Comgate;
use Comgate\SDK\Entity\Codes\PaymentMethodCode;
use Comgate\SDK\Entity\Response\PaymentStatusResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function getenv;
use function trim;
use function sha1;

abstract class ComgateBase
{
    protected const REFERENCE_ID_ATRIBUT = 'ref';

    protected const TRANSACTION_ID_ATRIBUT = 'tId';

    //vala04 - atribut pro provedení kontrol hashů
    protected const TRANSACTION_CODE_ATRIBUT = 'tC';

    protected const HASH_ATRIBUT = 'hash';

    protected const SYMBOL_DELIMITER = '/';

    protected UrlGeneratorInterface $urlGenerator;

    protected ?Client $client = null;

    protected ?PaymentCreateResponse $paymentSession = null;

    //vala04 - Platebni brana v testovacim rezimu pro ucely vyvoje. Menit v .env
    private bool $testMode;

    abstract public function novaPlatba(ComgatePlatba $comgatePlatba, ComgateReturnRoute $returnRoute): ComgatePlatbaStav;

    abstract public function jeNavratovyPozadavek(Request $request): bool;

    abstract public function overitStavPlatbyPodleRequestu(Request $request): ?ComgatePlatbaStav;

    abstract public function overitStavPlatbyPodleTransakce(string $transactionId): ?ComgatePlatbaStav;

    /**
     * @throws ComgateException
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->client = $this->getClientComgate();
        $this->urlGenerator = $urlGenerator;

        $testModeRaw = getenv('COMGATE_TEST_MODE');
        if ($testModeRaw === false) {
            throw new ComgateException('COMGATE_TEST_MODE není nastaveno v .env!');
        }

        $testModeRaw = trim($testModeRaw);
        if ($testModeRaw !== 'true' && $testModeRaw !== 'false') {
            throw new ComgateException('COMGATE_TEST_MODE musí mít hodnotu true nebo false!');
        }

        $this->testMode = $testModeRaw === 'true';
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * @throws ComgateException
     */
    private function getClientComgate(): Client
    {
        $merchant = getenv('COMGATE_MERCHANT');
        if ($merchant === false) {
            throw new ComgateException('COMGATE_MERCHANT není nastaveno v .env!');
        }

        $secret = getenv('COMGATE_SECRET');
        if ($secret === false) {
            throw new ComgateException('COMGATE_SECRET není nastaveno v .env!');
        }

        return Comgate::defaults()
            ->setMerchant(trim($merchant))
            ->setSecret(trim($secret))
            ->createClient();
    }

    /**
     * @param string $refId
     * @param string $tCode
     * @param string|null $hash
     * @return string|null
     * @throws ComgateException
     */
    protected function hashKontrola(string $refId, string $tCode, ?string $hash = null): ?string
    {
        $hashSalt = getenv('COMGATE_HASH_SALT');
        if ($hashSalt === false) {
            throw new ComgateException('COMGATE_HASH_SALT není nastaveno v .env!');
        }

        $saltedHash = sha1($tCode . $refId . $hashSalt);

        if ($hash === null) {
            return $saltedHash;
        }

        return ($hash === $saltedHash) ? $saltedHash : null;
    }

    /**
     * @param ComgateReturnRoute $returnRoute
     * @param string $refId
     * @param string $tCode
     * @return string
     * @throws ComgateException
     */
    protected function generateReturnUrl(ComgateReturnRoute $returnRoute, string $refId, string $tCode): string
    {
        $returnRoute->setRouteParameter(self::HASH_ATRIBUT, $this->hashKontrola($refId, $tCode));
        $returnRoute->setRouteParameter(self::TRANSACTION_CODE_ATRIBUT, $tCode);
        $returnRoute->setRouteParameter(self::TRANSACTION_ID_ATRIBUT, ComgatePlaceholder::DUMMY_TRANSACTION_ID);
        $returnRoute->setRouteParameter(self::REFERENCE_ID_ATRIBUT, ComgatePlaceholder::DUMMY_REFERENCE_ID);

        $url = $this->urlGenerator->generate(
            $returnRoute->getSymfonyRoute(),
            $returnRoute->getSymfonyRouteParameters(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return ComgatePlaceholder::replace($url);
    }

    protected function zalozitPlatbu(ComgatePlatba $comgatePlatba, string $transactionId): ComgatePlatbaStav
    {
        return new ComgatePlatbaStav(
            $transactionId,
            $comgatePlatba->getSpecifickySymbol(),
            $comgatePlatba->getVariabilniSymbol(),
            ComgatePlatbaStav::COMGATE_PLATBA_STAV_CEKAJICI_ID,
            ComgatePlatbaStav::COMGATE_PLATBA_STAV_CEKAJICI_POPIS,
            ComgatePlatbaStav::COMGATE_PLATBA_STAV_CEKAJICI_ZVYRAZNENI,
            true
        );
    }

    protected function getPlatebniMetody(bool $pouzePlatbaKartou): string
    {
        if ($pouzePlatbaKartou === true) {
            return PaymentMethodCode::ALL_CARDS;
        }

        return PaymentMethodCode::ALL . ' - ' . PaymentMethodCode::LOAN_ALL . ' - ' . PaymentMethodCode::LATER_ALL . ' - ' .
            PaymentMethodCode::PART_ALL . ' - ' . PaymentMethodCode::BANK_OTHER_CZ_TRANSFER . ' - BANK_CZ_AB_CVAK - PART_TWISTO - PART_ESSOX';
    }

    /**
     * @param PaymentStatusResponse $paymentStatusResponse
     * @return ComgatePlatbaStav
     * @throws ComgateException
     */
    protected function overitStavPlatby(PaymentStatusResponse $paymentStatusResponse): ComgatePlatbaStav
    {
        switch ($paymentStatusResponse->getStatus()) {
            case ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZAPLACENO_ID:
                return new ComgatePlatbaStav(
                    $paymentStatusResponse->getTransId(),
                    $paymentStatusResponse->getRefId(),
                    $paymentStatusResponse->getName(),
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZAPLACENO_ID,
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZAPLACENO_POPIS,
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZAPLACENO_ZVYRAZNENI,
                    false,
                    $paymentStatusResponse->getMethod(),
                    $paymentStatusResponse->getVs()
                );
            case ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZRUSENO_ID:
                return new ComgatePlatbaStav(
                    $paymentStatusResponse->getTransId(),
                    $paymentStatusResponse->getRefId(),
                    $paymentStatusResponse->getName(),
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZRUSENO_ID,
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZRUSENO_POPIS,
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_ZRUSENO_ZVYRAZNENI,
                    false,
                    $paymentStatusResponse->getMethod(),
                    $paymentStatusResponse->getVs()
                );
            case ComgatePlatbaStav::COMGATE_PLATBA_STAV_CEKAJICI_ID:
                return new ComgatePlatbaStav(
                    $paymentStatusResponse->getTransId(),
                    $paymentStatusResponse->getRefId(),
                    $paymentStatusResponse->getName(),
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_CEKAJICI_ID,
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_CEKAJICI_POPIS,
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_CEKAJICI_ZVYRAZNENI,
                    false,
                    $paymentStatusResponse->getMethod(),
                    $paymentStatusResponse->getVs()
                );
            case ComgatePlatbaStav::COMGATE_PLATBA_STAV_AUTORIZOVANO_ID:
                return new ComgatePlatbaStav(
                    $paymentStatusResponse->getTransId(),
                    $paymentStatusResponse->getRefId(),
                    $paymentStatusResponse->getName(),
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_AUTORIZOVANO_ID,
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_AUTORIZOVANO_POPIS,
                    ComgatePlatbaStav::COMGATE_PLATBA_STAV_AUTORIZOVANO_ZVYRAZNENI,
                    false,
                    $paymentStatusResponse->getMethod(),
                    $paymentStatusResponse->getVs()
                );
            default:
                throw new ComgateException('Neznámý stav platby při návratu z brány. ID: ' .
                    $paymentStatusResponse->getTransId() . ', Ref. ID: ' . $paymentStatusResponse->getRefId());
        }
    }
}
