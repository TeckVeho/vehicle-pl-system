<?php

namespace App\Http\Controllers;

use Google\Service\Drive;
use Google\Service\Oauth2;
use Google\Service\Script;
use Google\Service\Sheets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Repository\GoogleSpreadsheetService;
use Google_Client;
use App\Models\GoogleAuthLog;

class GoogleAuthSettingController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId(config('google.oauth2.client_id'));
        $this->googleClient->setClientSecret(config('google.oauth2.client_secret'));
        $this->googleClient->setRedirectUri(config('google.oauth2.redirect_uri'));
        $this->googleClient->setScopes(
            [
                Sheets::SPREADSHEETS,
                Drive::DRIVE_FILE,
                Drive::DRIVE,
                Oauth2::USERINFO_EMAIL,
                Script::SCRIPT_PROJECTS,
                Script::SCRIPT_DEPLOYMENTS,
                Script::SCRIPT_PROCESSES,
                Script::SCRIPT_METRICS
            ]
        );
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setPrompt('consent');
    }

    /**
     * Hiển thị trang setting OAuth2 Google
     */
    public function index()
    {
        $service = new GoogleSpreadsheetService();
        $isAuthenticated = false;
        $userInfo = null;
        $tokenInfo = null;
        $error = null;

        try {
            // Load token từ file
            $service->loadAccessTokenFromFile();
            $isAuthenticated = $service->isAuthenticated();

            if ($isAuthenticated) {
                $userInfo = $service->getCurrentUser();
                $tokenInfo = $this->getTokenInfo();
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error('GoogleAuthSettingController::index error: ' . $error);
        }

        return view('google-auth.settings', compact('isAuthenticated', 'userInfo', 'tokenInfo', 'error'));
    }

    /**
     * Xử lý authenticate
     */
    public function authenticate()
    {
        try {
            // Tạo URL authorize sử dụng Google Client riêng
            $authUrl = $this->googleClient->createAuthUrl();

            return redirect($authUrl);

        } catch (\Exception $e) {
            Log::error('GoogleAuthSettingController::authenticate error: ' . $e->getMessage());
            return back()->with('error', 'Unable to create authorize URL: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý callback từ Google
     */
    public function callback(Request $request)
    {
        try {
            if ($request->has('code')) {
                // Lấy access token sử dụng Google Client riêng
                $token = $this->googleClient->fetchAccessTokenWithAuthCode($request->get('code'));

                // Lưu token vào file
                $tokenPath = storage_path('app/google_oauth_token.json');
                file_put_contents($tokenPath, json_encode($token));

                // Lấy thông tin user từ Google
                $service = new GoogleSpreadsheetService();
                $service->loadAccessTokenFromFile();
                $userInfo = $service->getCurrentUser();

                // Lưu log vào database
                \App\Models\GoogleAuthLog::create([
                    'email' => $userInfo['email'] ?? 'unknown@example.com',
                    'name' => $userInfo['name'] ?? 'Unknown User',
                    'google_id' => $userInfo['id'] ?? null,
                    'picture' => $userInfo['picture'] ?? null,
                    'action' => 'login',
                    'token_info' => [
                        'access_token' => isset($token['access_token']) ? substr($token['access_token'], 0, 50) . '...' : null,
                        'refresh_token' => isset($token['refresh_token']) ? 'Yes' : 'No',
                        'expires_in' => $token['expires_in'] ?? null,
                        'token_type' => $token['token_type'] ?? null,
                        'scope' => $token['scope'] ?? null
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return redirect()->route('google-auth.settings')->with('success', 'Google authentication successful!');
            } else {
                return redirect()->route('google-auth.settings')->with('error', 'No authorization code received from Google');
            }

        } catch (\Exception $e) {
            Log::error('GoogleAuthSettingController::callback error: ' . $e->getMessage());
            return redirect()->route('google-auth.settings')->with('error', 'Error processing callback: ' . $e->getMessage());
        }
    }

    /**
     * Xóa token
     */
    public function logout()
    {
        try {
            // Lấy thông tin user trước khi logout
            $userInfo = null;
            $tokenPath = storage_path('app/google_oauth_token.json');

            if (file_exists($tokenPath)) {
                $service = new GoogleSpreadsheetService();
                $service->loadAccessTokenFromFile();

                if ($service->isAuthenticated()) {
                    $userInfo = $service->getCurrentUser();
                }
            }

            // Lưu log logout
            if ($userInfo) {
                \App\Models\GoogleAuthLog::create([
                    'email' => $userInfo['email'] ?? 'unknown@example.com',
                    'name' => $userInfo['name'] ?? 'Unknown User',
                    'google_id' => $userInfo['id'] ?? null,
                    'picture' => $userInfo['picture'] ?? null,
                    'action' => 'logout',
                    'token_info' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            // Xóa token file
            if (file_exists($tokenPath)) {
                unlink($tokenPath);
            }

            return redirect()->route('google-auth.settings')->with('success', 'Logged out successfully!');

        } catch (\Exception $e) {
            Log::error('GoogleAuthSettingController::logout error: ' . $e->getMessage());
            return redirect()->route('google-auth.settings')->with('error', 'Logout error: ' . $e->getMessage());
        }
    }

    /**
     * Test connection
     */
    public function testConnection()
    {
        try {
            // Sử dụng direct service call thay vì HTTP request
            $service = new GoogleSpreadsheetService();

            // Load token
            $service->loadAccessTokenFromFile();

            if (!$service->isAuthenticated()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid or expired'
                ]);
            }

            $userInfo = $service->getCurrentUser();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful!',
                'user' => $userInfo
            ]);

        } catch (\Exception $e) {
            Log::error('GoogleAuthSettingController::testConnection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Lấy thông tin token
     */
    private function getTokenInfo()
    {
        try {
            $tokenPath = storage_path('app/google_oauth_token.json');

            if (!file_exists($tokenPath)) {
                return null;
            }

            $token = json_decode(file_get_contents($tokenPath), true);

            if (!$token) {
                return null;
            }

            $tokenInfo = [
                'access_token' => isset($token['access_token']) ? substr($token['access_token'], 0, 50) . '...' : 'N/A',
                'refresh_token' => isset($token['refresh_token']) ? 'Yes' : 'No',
                'expires_in' => isset($token['expires_in']) ? $token['expires_in'] . ' seconds' : 'N/A',
                'token_type' => $token['token_type'] ?? 'N/A',
                'scope' => $token['scope'] ?? 'N/A',
                'created' => isset($token['created']) ? date('Y-m-d H:i:s', $token['created']) : 'N/A'
            ];

            if (isset($token['expires_in']) && isset($token['created'])) {
                $expiresAt = $token['created'] + $token['expires_in'];
                $tokenInfo['expires_at'] = date('Y-m-d H:i:s', $expiresAt);
                $tokenInfo['is_expired'] = time() > $expiresAt;
            }

            return $tokenInfo;

        } catch (\Exception $e) {
            Log::error('GoogleAuthSettingController::getTokenInfo error: ' . $e->getMessage());
            return null;
        }
    }
}
