<?php

namespace App\Http\Controllers;

use App\Services\GoogleOAuthService;
use Illuminate\Http\Request;

class GoogleCalendarController extends Controller
{
    public function redirectToGoogle()
    {
        $googleOAuthService = new GoogleOAuthService();
        $authUrl = $googleOAuthService->getAuthUrl();
        return redirect()->away($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->get('code');
        $googleOAuthService = new GoogleOAuthService();
        $googleOAuthService->setAccessToken($code);
        
        // Rediriger l'utilisateur vers la page de son calendrier Google
        return redirect()->route('events.create');
    }
}