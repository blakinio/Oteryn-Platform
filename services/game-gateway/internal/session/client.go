package session

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strings"

	"github.com/blakinio/oteryn-platform/services/game-gateway/internal/gateway"
)

type Client struct {
	baseURL string
	token   string
	http    *http.Client
}

func NewClient(baseURL, token string, httpClient *http.Client) *Client {
	return &Client{
		baseURL: strings.TrimRight(baseURL, "/"),
		token:   token,
		http:    httpClient,
	}
}

func (c *Client) Create(ctx context.Context, request gateway.SessionRequest) (gateway.Session, error) {
	payload := struct {
		ProtocolVersion int    `json:"protocol_version"`
		CanaryAccountID int64  `json:"canary_account_id"`
		WorldID         int64  `json:"world_id"`
		LoginAttemptID  string `json:"login_attempt_id"`
	}{
		ProtocolVersion: 1,
		CanaryAccountID: request.CanaryAccountID,
		WorldID:         request.WorldID,
		LoginAttemptID:  request.LoginAttemptID,
	}

	encoded, err := json.Marshal(payload)
	if err != nil {
		return gateway.Session{}, gateway.ErrUnavailable
	}

	httpRequest, err := http.NewRequestWithContext(
		ctx,
		http.MethodPost,
		c.baseURL+"/internal/v1/game-sessions",
		bytes.NewReader(encoded),
	)
	if err != nil {
		return gateway.Session{}, gateway.ErrUnavailable
	}
	httpRequest.Header.Set("Authorization", "Bearer "+c.token)
	httpRequest.Header.Set("Accept", "application/json")
	httpRequest.Header.Set("Content-Type", "application/json")

	response, err := c.http.Do(httpRequest)
	if err != nil {
		return gateway.Session{}, gateway.ErrUnavailable
	}
	defer response.Body.Close()

	limited := io.LimitReader(response.Body, 64*1024)
	if response.StatusCode != http.StatusOK {
		_, _ = io.Copy(io.Discard, limited)
		return gateway.Session{}, gateway.ErrUnavailable
	}

	var result struct {
		ProtocolVersion int             `json:"protocol_version"`
		Session         gateway.Session `json:"session"`
	}
	if err := json.NewDecoder(limited).Decode(&result); err != nil {
		return gateway.Session{}, gateway.ErrUnavailable
	}
	if result.ProtocolVersion != 1 {
		return gateway.Session{}, gateway.ErrUnavailable
	}
	return result.Session, nil
}

func (c *Client) Ready(ctx context.Context) error {
	request, err := http.NewRequestWithContext(ctx, http.MethodGet, c.baseURL+"/health", nil)
	if err != nil {
		return fmt.Errorf("session readiness request: %w", gateway.ErrUnavailable)
	}

	response, err := c.http.Do(request)
	if err != nil {
		return fmt.Errorf("session readiness: %w", gateway.ErrUnavailable)
	}
	defer response.Body.Close()
	_, _ = io.Copy(io.Discard, io.LimitReader(response.Body, 4096))

	if response.StatusCode != http.StatusOK {
		return gateway.ErrUnavailable
	}
	return nil
}
