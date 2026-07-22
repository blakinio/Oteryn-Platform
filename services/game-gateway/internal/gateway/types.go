package gateway

import (
	"context"
	"errors"
	"time"
)

var (
	ErrInvalidLogin = errors.New("invalid login")
	ErrUnavailable  = errors.New("login unavailable")
)

type Authorization struct {
	CanaryAccountID    int64
	SecurityGeneration int64
}

type World struct {
	ID     int64  `json:"id"`
	Slug   string `json:"slug"`
	Name   string `json:"name"`
	Region string `json:"region"`
	Host   string `json:"host"`
	Port   int    `json:"port"`
}

type Character struct {
	ID       int64  `json:"id"`
	Name     string `json:"name"`
	Level    int    `json:"level"`
	Vocation int    `json:"vocation"`
	WorldID  int64  `json:"world_id"`
}

type LoginContext struct {
	Worlds     []World
	Characters []Character
}

type SessionRequest struct {
	CanaryAccountID int64  `json:"canary_account_id"`
	WorldID         int64  `json:"world_id"`
	LoginAttemptID  string `json:"login_attempt_id"`
}

type Session struct {
	Credential string    `json:"credential"`
	ExpiresAt  time.Time `json:"expires_at"`
}

type LoginResponse struct {
	ProtocolVersion int         `json:"protocol_version"`
	Session         Session     `json:"session"`
	Worlds          []World     `json:"worlds"`
	Characters      []Character `json:"characters"`
}

type PlatformClient interface {
	Redeem(ctx context.Context, ticket string) (Authorization, error)
	LoginContext(ctx context.Context, canaryAccountID int64) (LoginContext, error)
	Ready(ctx context.Context) error
}

type SessionIssuer interface {
	Create(ctx context.Context, request SessionRequest) (Session, error)
	Ready(ctx context.Context) error
}
