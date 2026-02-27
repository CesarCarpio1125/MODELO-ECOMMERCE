<?php

namespace App\Http\Controllers;

use App\Http\Requests\Help\GetHelpRequest;
use App\Services\HelpService;

class HelpController extends Controller
{
    public function __construct(
        private HelpService $helpService
    ) {}

    /**
     * Display the help page.
     */
    public function index(GetHelpRequest $request)
    {
        $user = $request->user();

        $helpData = $this->helpService->getHelpContent($user);

        return inertia('Help/Index', [
            'helpSections' => $helpData['sections'],
            'faqs' => $helpData['faqs'],
            'contactInfo' => $helpData['contactInfo'],
        ]);
    }
}
