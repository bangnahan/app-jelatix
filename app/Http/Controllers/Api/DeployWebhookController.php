<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeployWebhookController extends Controller
{
    /**
     * Handle incoming deployment webhook from GitHub
     */
    public function handle(Request $request): JsonResponse
    {
        $expectedSecret = config('app.deploy_secret', env('DEPLOY_SECRET', 'jelatix_auto_deploy_secret_2026'));

        // 1. Verifikasi Keamanan: Cek X-Hub-Signature-256 dari GitHub atau token secret
        $githubSignature = $request->header('X-Hub-Signature-256');
        $providedSecret = $request->input('secret') ?: $request->header('X-Deploy-Secret');

        $isValid = false;

        if ($githubSignature) {
            $computedHash = 'sha256=' . hash_hmac('sha256', $request->getContent(), $expectedSecret);
            $isValid = hash_equals($computedHash, $githubSignature);
        } elseif ($providedSecret && hash_equals($expectedSecret, (string) $providedSecret)) {
            $isValid = true;
        }

        if (!$isValid) {
            Log::warning('Auto-deploy: Unauthorized access attempt', [
                'ip' => $request->ip(),
                'has_signature' => !empty($githubSignature),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Unauthorized signature or secret'], 403);
        }

        // 2. Hanya jalankan jika commit ditujukan ke branch main
        $ref = $request->input('ref');
        if ($ref && $ref !== 'refs/heads/main') {
            return response()->json([
                'status' => 'ignored',
                'message' => "Push event on branch [{$ref}] ignored. Only main branch is deployed.",
            ]);
        }

        // 3. Jalankan script deployment otomatis
        $basePath = base_path();
        $commands = [
            "cd {$basePath}",
            "git pull origin main 2>&1",
            "php artisan migrate --force 2>&1",
            "php artisan optimize 2>&1",
        ];

        $fullCommand = implode(' && ', $commands);
        $output = shell_exec($fullCommand);

        Log::info("Auto-deploy executed successfully via GitHub webhook:\n" . $output);

        return response()->json([
            'status' => 'success',
            'message' => 'Auto-deploy completed successfully',
            'branch' => 'main',
            'output' => $output,
        ]);
    }
}
