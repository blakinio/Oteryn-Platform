package httpapi

import (
	"bytes"
	"context"
	"encoding/json"
	"errors"
	"log/slog"
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"
	"time"

	"github.com/blakinio/oteryn-platform/services/game-gateway/internal/gateway"
)

type testPlatform struct {
	authorization gateway.Authorization
	loginContext  gateway.LoginContext
	redeemErr     error
	contextErr    error
	readyErr      error
}

func (f *testPlatform) Redeem(context.Context, string) (gateway.Authorization, error) {
	return f.authorization, f.redeemErr
}

func (f *testPlatform) LoginContext(context.Context, int64) (gateway.LoginContext, error) {
	return f.loginContext, f.contextErr
}

func (f *testPlatform) Ready(context.Context) error { return f.readyErr }

type testSessionIssuer struct {
	session  gateway.Session
	err      error
	readyErr error
}

func (f *testSessionIssuer) Create(context.Context, gateway.SessionRequest) (gateway.Session, error) {
	return f.session, f.err
}

func (f *testSessionIssuer) Ready(context.Context) error { return f.readyErr }

func TestLoginSuccessDoesNotLogCredentials(t *testing.T) {
	now := time.Now().UTC()
	platform := &testPlatform{
		authorization: gateway.Authorization{CanaryAccountID: 1001},
		loginContext: gateway.LoginContext{
			Worlds:     []gateway.World{{ID: 1, Slug: "oteryn", Name: "Oteryn", Region: "EU", Host: "game.example.test", Port: 7172}},
			Characters: []gateway.Character{{ID: 10, Name: "Alpha", Level: 100, Vocation: 4, WorldID: 1}},
		},
	}
	sessions := &testSessionIssuer{session: gateway.Session{Credential: "session-secret-never-log", ExpiresAt: now.Add(time.Minute)}}
	service := gateway.NewService(platform, sessions)
	var logs bytes.Buffer
	server := NewServer(service, "test-version", slog.New(slog.NewJSONHandler(&logs, nil)))

	body := `{"protocol_version":1,"game_login_ticket":"ticket-secret-never-log"}`
	request := httptest.NewRequest(http.MethodPost, "/v1/login", strings.NewReader(body))
	request.Header.Set("X-Request-ID", "request-123")
	response := httptest.NewRecorder()
	server.Handler().ServeHTTP(response, request)

	if response.Code != http.StatusOK {
		t.Fatalf("expected 200, got %d body=%s", response.Code, response.Body.String())
	}
	var payload gateway.LoginResponse
	if err := json.Unmarshal(response.Body.Bytes(), &payload); err != nil {
		t.Fatalf("decode response: %v", err)
	}
	if payload.Session.Credential != "session-secret-never-log" {
		t.Fatalf("unexpected session response: %#v", payload.Session)
	}
	logText := logs.String()
	if strings.Contains(logText, "ticket-secret-never-log") || strings.Contains(logText, "session-secret-never-log") {
		t.Fatalf("credential leaked to logs: %s", logText)
	}
	if !strings.Contains(logText, "request-123") || !strings.Contains(logText, `"path":"/v1/login"`) {
		t.Fatalf("bounded request metadata missing from logs: %s", logText)
	}
}

func TestLoginRejectsUnknownFieldsQueryAndOversizedBodyBeforeDependencies(t *testing.T) {
	platform := &testPlatform{redeemErr: errors.New("should not be called")}
	service := gateway.NewService(platform, &testSessionIssuer{})
	server := NewServer(service, "test", slog.New(slog.NewTextHandler(&bytes.Buffer{}, nil)))

	tests := []struct {
		name string
		url  string
		body string
	}{
		{name: "unknown field", url: "/v1/login", body: `{"protocol_version":1,"game_login_ticket":"ticket","password":"secret"}`},
		{name: "query", url: "/v1/login?ticket=secret", body: `{"protocol_version":1,"game_login_ticket":"ticket"}`},
		{name: "oversized", url: "/v1/login", body: `{"protocol_version":1,"game_login_ticket":"` + strings.Repeat("a", 5000) + `"}`},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			request := httptest.NewRequest(http.MethodPost, test.url, strings.NewReader(test.body))
			response := httptest.NewRecorder()
			server.Handler().ServeHTTP(response, request)
			if response.Code != http.StatusBadRequest {
				t.Fatalf("expected 400, got %d body=%s", response.Code, response.Body.String())
			}
		})
	}
}

func TestLoginMapsInvalidTicketAndDependencyOutageToBoundedErrors(t *testing.T) {
	for _, test := range []struct {
		name   string
		err    error
		status int
		body   string
	}{
		{name: "invalid", err: gateway.ErrInvalidLogin, status: http.StatusUnauthorized, body: "invalid_login"},
		{name: "outage", err: gateway.ErrUnavailable, status: http.StatusServiceUnavailable, body: "login_unavailable"},
	} {
		t.Run(test.name, func(t *testing.T) {
			service := gateway.NewService(&testPlatform{redeemErr: test.err}, &testSessionIssuer{})
			server := NewServer(service, "test", slog.New(slog.NewTextHandler(&bytes.Buffer{}, nil)))
			request := httptest.NewRequest(http.MethodPost, "/v1/login", strings.NewReader(`{"protocol_version":1,"game_login_ticket":"ticket"}`))
			response := httptest.NewRecorder()
			server.Handler().ServeHTTP(response, request)
			if response.Code != test.status || !strings.Contains(response.Body.String(), test.body) {
				t.Fatalf("unexpected response: status=%d body=%s", response.Code, response.Body.String())
			}
		})
	}
}

func TestHealthAndReadinessAreSeparate(t *testing.T) {
	service := gateway.NewService(&testPlatform{readyErr: gateway.ErrUnavailable}, &testSessionIssuer{})
	server := NewServer(service, "v1.2.3", slog.New(slog.NewTextHandler(&bytes.Buffer{}, nil)))

	health := httptest.NewRecorder()
	server.Handler().ServeHTTP(health, httptest.NewRequest(http.MethodGet, "/health", nil))
	if health.Code != http.StatusOK {
		t.Fatalf("health should remain process-local, got %d", health.Code)
	}

	ready := httptest.NewRecorder()
	server.Handler().ServeHTTP(ready, httptest.NewRequest(http.MethodGet, "/ready", nil))
	if ready.Code != http.StatusServiceUnavailable {
		t.Fatalf("readiness should fail on dependency outage, got %d", ready.Code)
	}

	version := httptest.NewRecorder()
	server.Handler().ServeHTTP(version, httptest.NewRequest(http.MethodGet, "/version", nil))
	if version.Code != http.StatusOK || !strings.Contains(version.Body.String(), "v1.2.3") {
		t.Fatalf("unexpected version response: %d %s", version.Code, version.Body.String())
	}
}
