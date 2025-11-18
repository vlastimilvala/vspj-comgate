<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate\Base;

use Vspj\PlatebniBrana\Comgate\Exception\ComgateException;
use Comgate\SDK\Client;
use Comgate\SDK\Comgate;
use Comgate\SDK\Entity\Codes\PaymentMethodCode;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function getenv;
use function trim;
use function sha1;

abstract class ComgateBase
{
    protected const REFERENCE_ID_ATRIBUT = 'ref';

    protected const TRANSACTION_ID_ATRIBUT = 'pId';

    protected const HASH_ATRIBUT = 'hash';

    protected const SYMBOL_DELIMITER = '/';

    protected const COMGATE_METHODS = PaymentMethodCode::ALL . ' - ' . PaymentMethodCode::LOAN_ALL . ' - ' . PaymentMethodCode::LATER_ALL . ' - ' .
    PaymentMethodCode::PART_ALL . ' - ' . PaymentMethodCode::BANK_OTHER_CZ_TRANSFER . ' - BANK_CZ_AB_CVAK - PART_TWISTO - PART_ESSOX';

    protected UrlGeneratorInterface $urlGenerator;

    protected ?Client $client = null;

    //vala04 - Platebni brana v testovacim rezimu pro ucely vyvoje. Menit v .env
    protected bool $testMode;

    abstract public function novaPlatba(ComgatePlatba $comgatePlatba, ComgateReturnRoute $returnRoute): RedirectResponse;

    abstract public function jeNavratovyPozadavek(Request $request): bool;

    abstract public function overitStavPlatby(Request $request): ?ComgatePlatbaStav;

    abstract protected function generateReturnUrl(ComgateReturnRoute $returnRoute, string $referenceId): string;

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
     * @throws ComgateException
     */
    protected function hashKontrola(string $referenceId, ?string $hash = null): ?string
    {
        $hashSalt = getenv('COMGATE_HASH_SALT');
        if ($hashSalt === false) {
            throw new ComgateException('COMGATE_HASH_SALT není nastaveno v .env!');
        }

        $saltedHash = sha1($referenceId . $hashSalt);

        if ($hash === null) {
            return $saltedHash;
        }

        return ($hash === $saltedHash) ? $saltedHash : null;
    }
}
