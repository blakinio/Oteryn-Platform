package gateway

import (
	"context"
	"crypto/rand"
	"encoding/hex"
	"fmt"
	"time"
)

type Service struct {
	platform PlatformClient
	sessions SessionIssuer
	now      func() time.Time
}

func NewService(platform PlatformClient, sessions SessionIssuer) *Service {
	return &Service{
		platform: platform,
		sessions: sessions,
		now:      time.Now,
	}
}

func (s *Service) Login(ctx context.Context, ticket string) (LoginResponse, error) {
	if ticket == "" {
		return LoginResponse{}, ErrInvalidLogin
	}

	authorization, err := s.platform.Redeem(ctx, ticket)
	if err != nil {
		return LoginResponse{}, err
	}
	if authorization.CanaryAccountID < 1 {
		return LoginResponse{}, ErrUnavailable
	}

	loginContext, err := s.platform.LoginContext(ctx, authorization.CanaryAccountID)
	if err != nil {
		return LoginResponse{}, err
	}
	if len(loginContext.Worlds) != 1 {
		return LoginResponse{}, ErrUnavailable
	}

	world := loginContext.Worlds[0]
	if world.ID < 1 || world.Host == "" || world.Port < 1 || world.Port > 65535 {
		return LoginResponse{}, ErrUnavailable
	}
	for _, character := range loginContext.Characters {
		if character.WorldID != world.ID || character.Name == "" {
			return LoginResponse{}, ErrUnavailable
		}
	}

	loginAttemptID, err := randomID(16)
	if err != nil {
		return LoginResponse{}, fmt.Errorf("generate login attempt id: %w", ErrUnavailable)
	}

	session, err := s.sessions.Create(ctx, SessionRequest{
		CanaryAccountID: authorization.CanaryAccountID,
		WorldID:         world.ID,
		LoginAttemptID:  loginAttemptID,
	})
	if err != nil {
		return LoginResponse{}, err
	}
	if session.Credential == "" || !session.ExpiresAt.After(s.now()) {
		return LoginResponse{}, ErrUnavailable
	}

	return LoginResponse{
		ProtocolVersion: 1,
		Session:         session,
		Worlds:          loginContext.Worlds,
		Characters:      loginContext.Characters,
	}, nil
}

func (s *Service) Ready(ctx context.Context) error {
	if err := s.platform.Ready(ctx); err != nil {
		return err
	}
	if err := s.sessions.Ready(ctx); err != nil {
		return err
	}
	return nil
}

func randomID(bytes int) (string, error) {
	buffer := make([]byte, bytes)
	if _, err := rand.Read(buffer); err != nil {
		return "", err
	}
	return hex.EncodeToString(buffer), nil
}
