package gateway

import (
	"context"
	"errors"
	"testing"
	"time"
)

type fakePlatform struct {
	authorization Authorization
	loginContext  LoginContext
	redeemErr     error
	contextErr    error
	readyErr      error
	redeemCalls   int
	contextCalls  int
}

func (f *fakePlatform) Redeem(context.Context, string) (Authorization, error) {
	f.redeemCalls++
	return f.authorization, f.redeemErr
}

func (f *fakePlatform) LoginContext(context.Context, int64) (LoginContext, error) {
	f.contextCalls++
	return f.loginContext, f.contextErr
}

func (f *fakePlatform) Ready(context.Context) error { return f.readyErr }

type fakeSessionIssuer struct {
	session Session
	err     error
	readyErr error
	calls   int
	request SessionRequest
}

func (f *fakeSessionIssuer) Create(_ context.Context, request SessionRequest) (Session, error) {
	f.calls++
	f.request = request
	return f.session, f.err
}

func (f *fakeSessionIssuer) Ready(context.Context) error { return f.readyErr }

func TestLoginSuccess(t *testing.T) {
	now := time.Date(2026, 7, 22, 8, 0, 0, 0, time.UTC)
	platform := &fakePlatform{
		authorization: Authorization{CanaryAccountID: 1001, SecurityGeneration: 7},
		loginContext: LoginContext{
			Worlds: []World{{ID: 1, Slug: "oteryn", Name: "Oteryn", Region: "EU", Host: "game.example.test", Port: 7172}},
			Characters: []Character{{ID: 10, Name: "Alpha", Level: 100, Vocation: 4, WorldID: 1}},
		},
	}
	sessions := &fakeSessionIssuer{session: Session{Credential: "session-secret", ExpiresAt: now.Add(time.Minute)}}
	service := NewService(platform, sessions)
	service.now = func() time.Time { return now }

	response, err := service.Login(context.Background(), "one-time-ticket")
	if err != nil {
		t.Fatalf("Login returned error: %v", err)
	}
	if response.ProtocolVersion != 1 || response.Session.Credential != "session-secret" {
		t.Fatalf("unexpected response: %#v", response)
	}
	if platform.redeemCalls != 1 || platform.contextCalls != 1 || sessions.calls != 1 {
		t.Fatalf("unexpected dependency calls: redeem=%d context=%d session=%d", platform.redeemCalls, platform.contextCalls, sessions.calls)
	}
	if sessions.request.CanaryAccountID != 1001 || sessions.request.WorldID != 1 || sessions.request.LoginAttemptID == "" {
		t.Fatalf("unexpected session request: %#v", sessions.request)
	}
}

func TestLoginStopsAfterInvalidTicket(t *testing.T) {
	platform := &fakePlatform{redeemErr: ErrInvalidLogin}
	sessions := &fakeSessionIssuer{}
	service := NewService(platform, sessions)

	_, err := service.Login(context.Background(), "invalid")
	if !errors.Is(err, ErrInvalidLogin) {
		t.Fatalf("expected ErrInvalidLogin, got %v", err)
	}
	if platform.contextCalls != 0 || sessions.calls != 0 {
		t.Fatalf("downstream dependencies were called after invalid ticket")
	}
}

func TestLoginFailsClosedForAmbiguousWorlds(t *testing.T) {
	platform := &fakePlatform{
		authorization: Authorization{CanaryAccountID: 1001},
		loginContext: LoginContext{Worlds: []World{
			{ID: 1, Host: "one.test", Port: 7172},
			{ID: 2, Host: "two.test", Port: 7172},
		}},
	}
	sessions := &fakeSessionIssuer{}
	service := NewService(platform, sessions)

	_, err := service.Login(context.Background(), "ticket")
	if !errors.Is(err, ErrUnavailable) {
		t.Fatalf("expected ErrUnavailable, got %v", err)
	}
	if sessions.calls != 0 {
		t.Fatalf("session issuer called for ambiguous multiworld context")
	}
}

func TestLoginFailsClosedForCharacterWorldMismatch(t *testing.T) {
	platform := &fakePlatform{
		authorization: Authorization{CanaryAccountID: 1001},
		loginContext: LoginContext{
			Worlds: []World{{ID: 1, Host: "one.test", Port: 7172}},
			Characters: []Character{{ID: 1, Name: "Alpha", WorldID: 2}},
		},
	}
	sessions := &fakeSessionIssuer{}
	service := NewService(platform, sessions)

	_, err := service.Login(context.Background(), "ticket")
	if !errors.Is(err, ErrUnavailable) {
		t.Fatalf("expected ErrUnavailable, got %v", err)
	}
	if sessions.calls != 0 {
		t.Fatalf("session issuer called for mismatched character world")
	}
}

func TestReadyFailsWhenAnyDependencyIsUnavailable(t *testing.T) {
	platform := &fakePlatform{}
	sessions := &fakeSessionIssuer{readyErr: ErrUnavailable}
	service := NewService(platform, sessions)

	if err := service.Ready(context.Background()); !errors.Is(err, ErrUnavailable) {
		t.Fatalf("expected readiness failure, got %v", err)
	}
}
