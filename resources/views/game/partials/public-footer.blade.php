<footer class="site-footer">
    <div class="site-footer-inner public-footer-grid">
        <div class="public-footer-brand">
            <img class="brand-wordmark-art" src="{{ asset('images/oteryn-wordmark.svg') }}" alt="Oteryn Platform">
            <p>Public world information and secure account services.</p>
            <p class="public-footer-status-note">Live game and service data can become temporarily unavailable. The portal does not replace missing data with fabricated values.</p>
        </div>

        @foreach ($footerGroups as $group)
            <nav class="public-footer-group" aria-label="{{ $group['label'] }} links">
                <h2>{{ $group['label'] }}</h2>
                @foreach ($group['items'] as $item)
                    <a href="{{ $item['url'] }}" @if(request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
        @endforeach

        <nav class="public-footer-group" aria-label="Account links">
            <h2>Account</h2>
            @guest
                <a href="{{ route('identity.login.create') }}">Sign in</a>
                <a href="{{ route('identity.register.create') }}">Create account</a>
                <a href="{{ route('password.request') }}">Recover password</a>
            @else
                <a href="{{ route('account.overview') }}">Account overview</a>
                <a href="{{ route('identity.mfa.settings') }}">Account security</a>
                <a href="{{ route('identity.password.change.create') }}">Change password</a>
            @endguest
        </nav>
    </div>

    <div class="public-footer-meta">
        <div>
            <span>&copy; {{ now()->year }} Oteryn Platform</span>
            <span>Language: {{ strtoupper(app()->getLocale()) }}</span>
        </div>
    </div>
</footer>
