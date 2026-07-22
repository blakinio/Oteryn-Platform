package session

import (
	"context"
	"encoding/json"
	"errors"
	"net/http"
	"net/http/httptest"
	"testing"
	"time"

	"github.com/blakinio/oteryn-platform/services/game-gateway/internal/gateway"
)

func TestCreateUsesServiceAuthenticationAndBoundedSessionContract(t *testing.T) {
	expiresAt := time.Now().UTC().Add(time.Minute).Truncate(time.Second)
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodPost || r.URL.Path != "/internal/v1/game-sessions" || r.URL.RawQuery != "" {
			t.Fatalf("unexpected request: %s %s", r.Method, r.URL.String())
		}
		if r.Header.Get("Authorization") != "Bearer session-service-token" {
			t.Fatalf("missing session service authentication")
		}
		var payload struct {
			ProtocolVersion int    `json:"protocol_version"`
			CanaryAccountID int64  `json:"canary_account_id"`
			WorldID         int64  `json:"world_id"`
			LoginAttemptID  string `json:"login_attempt_id"`
		}
		if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
			t.Fatalf("decode request: %v", err)
		}
		if payload.ProtocolVersion != 1 || payload.CanaryAccountID != 1001 || payload.WorldID != 1 || payload.LoginAttemptID != "attempt-123" {
			t.Fatalf("unexpected session payload: %#v", payload)
		}
		w.Header().Set("Content-Type", "application/json")
		_ = json.NewEncoder(w).Encode(map[string]any{
			"protocol_version": 1,
			"session": map[string]any{
				"credential": "session-secret",
				"expires_at": expiresAt,
			},
		})
	}))
	defer server.Close()

	client := NewClient(server.URL, "session-service-token", server.Client())
	created, err := client.Create(context.Background(), gateway.SessionRequest{
		CanaryAccountID: 1001,
		WorldID:         1,
		LoginAttemptID:  "attempt-123",
	})
	if err != nil {
		t.Fatalf("Create returned error: %v", err)
	}
	if created.Credential != "session-secret" || !created.ExpiresAt.Equal(expiresAt) {
		t.Fatalf("unexpected session: %#v", created)
	}
}

func TestCreateFailsClosedOnDependencyErrors(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, _ *http.Request) {
		w.WriteHeader(http.StatusServiceUnavailable)
	}))
	defer server.Close()

	client := NewClient(server.URL, "session-service-token", server.Client())
	_, err := client.Create(context.Background(), gateway.SessionRequest{CanaryAccountID: 1001, WorldID: 1, LoginAttemptID: "attempt"})
	if !errors.Is(err, gateway.ErrUnavailable) {
		t.Fatalf("expected ErrUnavailable, got %v", err)
	}
}

func TestReadyChecksSessionServiceHealth(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/health" {
			t.Fatalf("unexpected readiness path: %s", r.URL.Path)
		}
		w.WriteHeader(http.StatusOK)
	}))
	defer server.Close()

	client := NewClient(server.URL, "session-service-token", server.Client())
	if err := client.Ready(context.Background()); err != nil {
		t.Fatalf("Ready returned error: %v", err)
	}
}
