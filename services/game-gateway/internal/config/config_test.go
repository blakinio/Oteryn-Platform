package config

import "testing"

func TestLoadRequiresServiceCredentialsAndValidDependencyURLs(t *testing.T) {
	t.Setenv("OTERYN_PLATFORM_BASE_URL", "https://platform.example.test")
	t.Setenv("GAME_SESSION_SERVICE_BASE_URL", "https://session.example.test")
	t.Setenv("OTERYN_PLATFORM_SERVICE_TOKEN", "platform-token")
	t.Setenv("GAME_SESSION_SERVICE_TOKEN", "session-token")
	t.Setenv("GATEWAY_REQUEST_TIMEOUT", "3s")

	cfg, err := Load()
	if err != nil {
		t.Fatalf("Load returned error: %v", err)
	}
	if cfg.PlatformBaseURL != "https://platform.example.test" || cfg.SessionBaseURL != "https://session.example.test" {
		t.Fatalf("unexpected dependency URLs: %#v", cfg)
	}
	if cfg.RequestTimeout.String() != "3s" {
		t.Fatalf("unexpected timeout: %s", cfg.RequestTimeout)
	}
}

func TestLoadAllowsHTTPOnlyForLoopbackDependencies(t *testing.T) {
	for _, raw := range []string{
		"http://127.0.0.1:8000",
		"http://localhost:8000",
		"http://[::1]:8000",
	} {
		t.Run(raw, func(t *testing.T) {
			t.Setenv("OTERYN_PLATFORM_BASE_URL", raw)
			t.Setenv("GAME_SESSION_SERVICE_BASE_URL", raw)
			t.Setenv("OTERYN_PLATFORM_SERVICE_TOKEN", "platform-token")
			t.Setenv("GAME_SESSION_SERVICE_TOKEN", "session-token")

			if _, err := Load(); err != nil {
				t.Fatalf("expected loopback URL %q to be accepted: %v", raw, err)
			}
		})
	}
}

func TestLoadRejectsHTTPForNonLoopbackDependencies(t *testing.T) {
	for _, raw := range []string{
		"http://platform.internal:8000",
		"http://10.0.0.10:8000",
		"http://192.168.1.10:8000",
	} {
		t.Run(raw, func(t *testing.T) {
			t.Setenv("OTERYN_PLATFORM_BASE_URL", raw)
			t.Setenv("GAME_SESSION_SERVICE_BASE_URL", "https://session.example.test")
			t.Setenv("OTERYN_PLATFORM_SERVICE_TOKEN", "platform-token")
			t.Setenv("GAME_SESSION_SERVICE_TOKEN", "session-token")

			if _, err := Load(); err == nil {
				t.Fatalf("expected non-loopback HTTP URL %q to be rejected", raw)
			}
		})
	}
}

func TestLoadFailsClosedForMissingCredentials(t *testing.T) {
	t.Setenv("OTERYN_PLATFORM_BASE_URL", "https://platform.example.test")
	t.Setenv("GAME_SESSION_SERVICE_BASE_URL", "https://session.example.test")
	t.Setenv("OTERYN_PLATFORM_SERVICE_TOKEN", "")
	t.Setenv("GAME_SESSION_SERVICE_TOKEN", "")

	if _, err := Load(); err == nil {
		t.Fatal("expected missing service credentials to fail")
	}
}

func TestLoadRejectsCredentialBearingOrQueryDependencyURLs(t *testing.T) {
	for _, raw := range []string{
		"https://user:password@platform.example.test",
		"https://platform.example.test?token=secret",
		"file:///tmp/platform.sock",
	} {
		t.Run(raw, func(t *testing.T) {
			t.Setenv("OTERYN_PLATFORM_BASE_URL", raw)
			t.Setenv("GAME_SESSION_SERVICE_BASE_URL", "https://session.example.test")
			t.Setenv("OTERYN_PLATFORM_SERVICE_TOKEN", "platform-token")
			t.Setenv("GAME_SESSION_SERVICE_TOKEN", "session-token")

			if _, err := Load(); err == nil {
				t.Fatalf("expected URL %q to be rejected", raw)
			}
		})
	}
}

func TestLoadRejectsUnboundedRequestTimeout(t *testing.T) {
	t.Setenv("OTERYN_PLATFORM_BASE_URL", "https://platform.example.test")
	t.Setenv("GAME_SESSION_SERVICE_BASE_URL", "https://session.example.test")
	t.Setenv("OTERYN_PLATFORM_SERVICE_TOKEN", "platform-token")
	t.Setenv("GAME_SESSION_SERVICE_TOKEN", "session-token")
	t.Setenv("GATEWAY_REQUEST_TIMEOUT", "2m")

	if _, err := Load(); err == nil {
		t.Fatal("expected excessive request timeout to fail")
	}
}
