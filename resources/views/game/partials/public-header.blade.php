<header class="site-header">
    <div class="header-inner">
        <a class="brand portal-brand" href="{{ route('home') }}" aria-label="Oteryn Platform home">
            <img class="brand-wordmark-art" src="{{ asset('images/oteryn-wordmark.svg') }}" alt="" aria-hidden="true">
        </a>

        <nav class="primary-nav desktop-only" aria-label="Public navigation">
            @foreach ($headerItems as $item)
                <a class="nav-link" href="{{ $item['url'] }}" @if(request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="account-actions desktop-only">
            @guest
                <a class="nav-link" href="{{ route('identity.login.create') }}" @if(request()->routeIs('identity.login.*')) aria-current="page" @endif>Sign in</a>
                <a class="button" href="{{ route('identity.register.create') }}">Create account</a>
            @else
                <a class="button button-secondary" href="{{ route('account.overview') }}">Account</a>
                <form method="POST" action="{{ route('identity.logout') }}">
                    @csrf
                    <button class="button-ghost" type="submit">Sign out</button>
                </form>
            @endguest
        </div>

        <details class="mobile-nav">
            <summary aria-label="Open navigation">Menu</summary>
            <div class="mobile-nav-panel">
                <nav aria-label="Mobile public navigation">
                    @foreach ($headerItems as $item)
                        <a class="nav-link" href="{{ $item['url'] }}" @if(request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="mobile-account-actions">
                    @guest
                        <a class="nav-link" href="{{ route('identity.login.create') }}">Sign in</a>
                        <a class="nav-link" href="{{ route('identity.register.create') }}">Create account</a>
                    @else
                        <a class="nav-link" href="{{ route('account.overview') }}">Account overview</a>
                        <a class="nav-link" href="{{ route('identity.mfa.settings') }}">Account security</a>
                        <a class="nav-link" href="{{ route('identity.password.change.create') }}">Change password</a>
                        <form method="POST" action="{{ route('identity.logout') }}">
                            @csrf
                            <button class="button-ghost" type="submit">Sign out</button>
                        </form>
                    @endguest
                </div>
            </div>
        </details>
    </div>
</header>
