<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorize — {{ $app_name }}</title>
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        .auth-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(148, 163, 184, 0.05),
                0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Header */
        .auth-header {
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        }

        .app-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
        }

        .app-icon svg {
            width: 28px;
            height: 28px;
            color: #fff;
        }

        .auth-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.375rem;
        }

        .auth-header p {
            font-size: 0.875rem;
            color: #94a3b8;
            line-height: 1.5;
        }

        .client-name {
            color: #a5b4fc;
            font-weight: 600;
        }

        /* VIP Badge */
        .vip-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.1));
            border: 1px solid rgba(251, 191, 36, 0.25);
            color: #fbbf24;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            margin-bottom: 1rem;
            letter-spacing: 0.025em;
        }

        .vip-badge svg {
            width: 14px;
            height: 14px;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: rgba(15, 23, 42, 0.4);
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .user-details {
            min-width: 0;
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 0.75rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-label {
            margin-left: auto;
            font-size: 0.6875rem;
            color: #64748b;
            white-space: nowrap;
        }

        /* Scopes */
        .scopes-section {
            padding: 1.5rem 2rem;
        }

        .scopes-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .scopes-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .scope-item {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.625rem 0.75rem;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(148, 163, 184, 0.06);
            border-radius: 0.625rem;
            transition: border-color 0.15s;
        }

        .scope-item:hover {
            border-color: rgba(148, 163, 184, 0.12);
        }

        .scope-icon {
            width: 20px;
            height: 20px;
            color: #6366f1;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .scope-text {
            min-width: 0;
        }

        .scope-id {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #e2e8f0;
        }

        .scope-desc {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.125rem;
            line-height: 1.4;
        }

        .no-scopes {
            text-align: center;
            padding: 1rem;
            font-size: 0.8125rem;
            color: #64748b;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 0.625rem;
        }

        /* Actions */
        .auth-actions {
            padding: 0 2rem 2rem;
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            flex: 1;
            padding: 0.6875rem 1.25rem;
            border: none;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            text-align: center;
        }

        .btn-deny {
            background: rgba(148, 163, 184, 0.08);
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .btn-deny:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.2);
        }

        .btn-approve {
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            color: #fff;
            border: 1px solid transparent;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-approve:hover {
            background: linear-gradient(135deg, #818cf8, #8b5cf6);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            transform: translateY(-1px);
        }

        .btn-approve:active {
            transform: translateY(0);
        }

        /* Footer */
        .auth-footer {
            padding: 1rem 2rem;
            text-align: center;
            border-top: 1px solid rgba(148, 163, 184, 0.08);
            background: rgba(15, 23, 42, 0.3);
        }

        .auth-footer p {
            font-size: 0.6875rem;
            color: #475569;
            line-height: 1.6;
        }

        .auth-footer a {
            color: #6366f1;
            text-decoration: none;
        }

        .auth-footer a:hover {
            color: #818cf8;
            text-decoration: underline;
        }

        /* Powered by */
        .powered-by {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.6875rem;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            {{-- Header --}}
            <div class="auth-header">
                @if($is_vip_user)
                    <span class="vip-badge">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/></svg>
                        VIP
                    </span>
                @endif

                <div class="app-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>

                <h1>Authorization Request</h1>
                <p>
                    <span class="client-name">{{ $client->name }}</span>
                    is requesting permission to access your account.
                </p>
            </div>

            {{-- Logged-in user --}}
            <div class="user-info">
                <div class="user-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="user-details">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-email">{{ $user->email }}</div>
                </div>
                <span class="user-label">Signed in</span>
            </div>

            {{-- Scopes --}}
            <div class="scopes-section">
                <div class="scopes-label">This will allow the app to:</div>

                @if(count($scopes) > 0)
                    <ul class="scopes-list">
                        @foreach($scopes as $scope)
                            <li class="scope-item">
                                <svg class="scope-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                                <div class="scope-text">
                                    <div class="scope-id">{{ $scope->id }}</div>
                                    @if($scope->description)
                                        <div class="scope-desc">{{ $scope->description }}</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="no-scopes">No special permissions requested.</div>
                @endif
            </div>

            {{-- Approve / Deny --}}
            <div class="auth-actions">
                <form method="POST" action="{{ route('passport.authorizations.deny') }}" style="flex:1;display:flex;">
                    @csrf
                    <input type="hidden" name="state"    value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="btn btn-deny" style="width:100%;">Deny</button>
                </form>

                <form method="POST" action="{{ route('passport.authorizations.approve') }}" style="flex:1;display:flex;">
                    @csrf
                    <input type="hidden" name="state"    value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="btn btn-approve" style="width:100%;">Authorize</button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="auth-footer">
                <p>
                    By authorizing, you agree to share the listed data with
                    <strong>{{ $client->name }}</strong>.<br>
                    You can revoke access at any time.
                    <a href="mailto:{{ $support_email }}">Need help?</a>
                </p>
            </div>
        </div>

        <p class="powered-by">Secured by {{ $app_name }}</p>
    </div>
</body>
</html>
