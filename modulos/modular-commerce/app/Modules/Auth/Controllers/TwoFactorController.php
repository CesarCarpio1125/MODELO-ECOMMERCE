<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\TwoFactorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactorService
    ) {}

    public function showSetup()
    {
        $user = auth()->user();

        if ($user->google2fa_secret) {
            return redirect()->route('dashboard');
        }

        $qrCodeUrl = $this->twoFactorService->generateQrCodeUrl($user);

        return Inertia::render('Auth/TwoFactorSetup', [
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    public function storeSetup(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($this->twoFactorService->verifyCode($user, $request->code)) {
            $this->twoFactorService->enableTwoFactor($user);

            return redirect()->route('dashboard')->with('status', '2FA enabled successfully.');
        }

        return back()->withErrors(['code' => 'Invalid code.']);
    }

    public function showVerify()
    {
        return Inertia::render('Auth/TwoFactorVerify');
    }

    public function storeVerify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($this->twoFactorService->verifyCode($user, $request->code)) {
            session(['2fa_verified' => true]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['code' => 'Invalid code.']);
    }

    public function destroy()
    {
        $user = auth()->user();
        $this->twoFactorService->disableTwoFactor($user);

        return back()->with('status', '2FA disabled.');
    }
}
