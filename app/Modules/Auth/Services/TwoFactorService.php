<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use PragmaRX\Google2FALaravel\Google2FA;

class TwoFactorService
{
    public function __construct(
        private Google2FA $google2fa
    ) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function generateQrCodeUrl(User $user): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );
    }

    public function verifyCode(User $user, string $code): bool
    {
        return $this->google2fa->verifyKey($user->google2fa_secret, $code);
    }

    public function enableTwoFactor(User $user): void
    {
        $user->update([
            'google2fa_secret' => $this->generateSecret(),
            'google2fa_recovery_codes' => json_encode($this->generateRecoveryCodes()),
        ]);
    }

    public function disableTwoFactor(User $user): void
    {
        $user->update([
            'google2fa_secret' => null,
            'google2fa_recovery_codes' => null,
        ]);
    }

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(md5(microtime()), 0, 10));
        }

        return $codes;
    }
}
