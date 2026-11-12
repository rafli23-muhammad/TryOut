<?php

namespace App\Services;

class BimbelTokenService
{
    /**
     * Create a new token in data store
     */
    public static function createToken(array $payload): array
    {
        $data = TryoutDataStore::read();
        $tokens = $data['bimbel_tokens'] ?? [];

        $code = strtoupper(trim($payload['code'] ?? ''));
        if ($code === '') {
            $code = self::generateCode($payload['prefix'] ?? 'BIMBEL');
        }

        $token = [
            'id' => 'TOK-' . strtoupper(substr(md5($code . microtime(true)), 0, 8)),
            'code' => $code,
            'max_uses' => isset($payload['max_uses']) ? (int) $payload['max_uses'] : 0,
            'used_count' => 0,
            'expires_at' => !empty($payload['expires_at']) ? $payload['expires_at'] : null,
            'notes' => trim($payload['notes'] ?? ''),
            'is_active' => isset($payload['is_active']) ? (bool) $payload['is_active'] : true,
            'claimed_users' => [],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $tokens[] = $token;
        $data['bimbel_tokens'] = $tokens;
        TryoutDataStore::write($data);

        return $token;
    }

    /**
     * Generate unique random code
     */
    public static function generateCode(string $prefix = 'BIMBEL'): string
    {
        $prefix = strtoupper(trim($prefix)) ?: 'BIMBEL';
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        return $prefix . '-' . $random;
    }

    /**
     * Validate and claim a bimbel token for a user
     */
    public static function claimToken(string $tokenCode, string $userId, ?string $userName = null): array
    {
        $tokenCode = strtoupper(trim($tokenCode));
        if ($tokenCode === '') {
            return ['success' => false, 'message' => 'Kode token tidak boleh kosong.'];
        }

        $data = TryoutDataStore::read();
        $tokens = $data['bimbel_tokens'] ?? [];
        $foundIndex = -1;

        foreach ($tokens as $idx => $t) {
            if (strtoupper($t['code']) === $tokenCode) {
                $foundIndex = $idx;
                break;
            }
        }

        if ($foundIndex === -1) {
            return ['success' => false, 'message' => 'Kode token tidak ditemukan atau salah.'];
        }

        $token = &$tokens[$foundIndex];

        // Check active status
        if (isset($token['is_active']) && !$token['is_active']) {
            return ['success' => false, 'message' => 'Token ini sudah dinonaktifkan oleh administrator.'];
        }

        // Check expiration
        if (!empty($token['expires_at'])) {
            $expireTime = strtotime($token['expires_at'] . ' 23:59:59');
            if ($expireTime && time() > $expireTime) {
                return ['success' => false, 'message' => 'Masa berlaku token ini sudah berakhir (' . date('d M Y', $expireTime) . ').'];
            }
        }

        // Check quota (0 or null = unlimited)
        $maxUses = (int) ($token['max_uses'] ?? 0);
        $usedCount = (int) ($token['used_count'] ?? 0);
        if ($maxUses > 0 && $usedCount >= $maxUses) {
            return ['success' => false, 'message' => 'Kuota penggunaan token ini sudah habis (' . $usedCount . '/' . $maxUses . ').'];
        }

        // Check if user already claimed this token
        $claimedUsers = $token['claimed_users'] ?? [];
        $alreadyClaimed = collect($claimedUsers)->contains(fn($c) => ($c['user_id'] ?? '') === $userId);
        if ($alreadyClaimed) {
            return ['success' => true, 'already' => true, 'message' => 'Anda sudah mengaktifkan token ini sebelumnya.'];
        }

        // Update token usage
        $token['used_count'] = $usedCount + 1;
        $token['claimed_users'][] = [
            'user_id' => $userId,
            'name' => $userName ?: 'Siswa',
            'claimed_at' => date('Y-m-d H:i:s'),
        ];
        $data['bimbel_tokens'] = $tokens;

        // Upgrade user status in data store
        $userUpdated = false;
        if (!empty($data['users'])) {
            foreach ($data['users'] as &$u) {
                if ($u['id'] === $userId) {
                    $u['student_type'] = 'bimbel';
                    $u['bimbel_token'] = $tokenCode;
                    $u['bimbel_joined_at'] = date('Y-m-d H:i:s');
                    $userUpdated = true;
                    break;
                }
            }
            unset($u);
        }

        // Auto-approve all existing/future packages for this bimbel student
        $packages = $data['packages'] ?? [];
        $orders = $data['orders'] ?? [];
        $now = date('Y-m-d H:i:s');

        foreach ($packages as $pkg) {
            $hasOrder = collect($orders)->contains(fn($o) => $o['student_id'] === $userId && $o['package_id'] === $pkg['id'] && $o['status'] === 'approved');
            if (!$hasOrder) {
                $orders[] = [
                    'id' => 'TRX-BIMBEL-' . strtoupper(substr(md5($userId . $pkg['id'] . time()), 0, 8)),
                    'student_id' => $userId,
                    'student_name' => $userName ?: 'Siswa Bimbel',
                    'student_email' => session('student_email', ''),
                    'package_id' => $pkg['id'],
                    'package_title' => $pkg['title'],
                    'amount' => 0,
                    'payment_method' => 'Token Bimbel (' . $tokenCode . ')',
                    'payment_proof' => null,
                    'status' => 'approved',
                    'created_at' => $now,
                    'approved_at' => $now,
                ];
            }
        }
        $data['orders'] = $orders;

        TryoutDataStore::write($data);

        // Sync session if currently logged in
        if (session('student_id') === $userId) {
            session([
                'student_type' => 'bimbel',
                'bimbel_token' => $tokenCode,
            ]);
        }

        return [
            'success' => true,
            'token' => $token,
            'message' => 'Selamat! Status Siswa Bimbel berhasil diaktifkan. Semua paket Try Out kini GRATIS untuk Anda!'
        ];
    }

    /**
     * Check if a user is a bimbel student
     */
    public static function isBimbelStudent(?string $userId): bool
    {
        if (!$userId) return false;

        if (session('student_id') === $userId && session('student_type') === 'bimbel') {
            return true;
        }

        $data = TryoutDataStore::read();
        $user = collect($data['users'] ?? [])->firstWhere('id', $userId);
        return ($user['student_type'] ?? 'umum') === 'bimbel';
    }
}
