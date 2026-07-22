package httpapi

import (
	"crypto/rand"
	"encoding/hex"
	"encoding/json"
	"errors"
	"io"
	"log/slog"
	"net/http"
	"regexp"
	"time"

	"github.com/blakinio/oteryn-platform/services/game-gateway/internal/gateway"
)

var safeRequestID = regexp.MustCompile(`\A[A-Za-z0-9._-]{1,64}\z`)

type Server struct {
	service *gateway.Service
	version string
	logger  *slog.Logger
	mux     *http.ServeMux
}

func NewServer(service *gateway.Service, version string, logger *slog.Logger) *Server {
	server := &Server{
		service: service,
		version: version,
		logger:  logger,
		mux:     http.NewServeMux(),
	}
	server.mux.HandleFunc("GET /health", server.health)
	server.mux.HandleFunc("GET /ready", server.ready)
	server.mux.HandleFunc("GET /version", server.versionInfo)
	server.mux.HandleFunc("POST /v1/login", server.login)
	return server
}

func (s *Server) Handler() http.Handler {
	return s.loggingMiddleware(s.mux)
}

func (s *Server) health(w http.ResponseWriter, _ *http.Request) {
	writeJSON(w, http.StatusOK, map[string]any{"status": "ok"})
}

func (s *Server) ready(w http.ResponseWriter, r *http.Request) {
	if err := s.service.Ready(r.Context()); err != nil {
		writeJSON(w, http.StatusServiceUnavailable, map[string]any{"status": "not_ready"})
		return
	}
	writeJSON(w, http.StatusOK, map[string]any{"status": "ready"})
}

func (s *Server) versionInfo(w http.ResponseWriter, _ *http.Request) {
	writeJSON(w, http.StatusOK, map[string]any{
		"service": "oteryn-game-gateway",
		"version": s.version,
	})
}

func (s *Server) login(w http.ResponseWriter, r *http.Request) {
	if r.URL.RawQuery != "" {
		writeJSON(w, http.StatusBadRequest, map[string]any{"error": "invalid_request"})
		return
	}

	var request struct {
		ProtocolVersion  int    `json:"protocol_version"`
		GameLoginTicket string `json:"game_login_ticket"`
	}
	decoder := json.NewDecoder(io.LimitReader(r.Body, 4096))
	decoder.DisallowUnknownFields()
	if err := decoder.Decode(&request); err != nil {
		writeJSON(w, http.StatusBadRequest, map[string]any{"error": "invalid_request"})
		return
	}
	if err := ensureJSONEOF(decoder); err != nil {
		writeJSON(w, http.StatusBadRequest, map[string]any{"error": "invalid_request"})
		return
	}
	if request.ProtocolVersion != 1 || request.GameLoginTicket == "" || len(request.GameLoginTicket) > 1024 {
		writeJSON(w, http.StatusBadRequest, map[string]any{"error": "invalid_request"})
		return
	}

	response, err := s.service.Login(r.Context(), request.GameLoginTicket)
	if err != nil {
		switch {
		case errors.Is(err, gateway.ErrInvalidLogin):
			writeJSON(w, http.StatusUnauthorized, map[string]any{"error": "invalid_login"})
		default:
			writeJSON(w, http.StatusServiceUnavailable, map[string]any{"error": "login_unavailable"})
		}
		return
	}

	writeJSON(w, http.StatusOK, response)
}

func ensureJSONEOF(decoder *json.Decoder) error {
	var extra any
	if err := decoder.Decode(&extra); !errors.Is(err, io.EOF) {
		return errors.New("request contains trailing JSON")
	}
	return nil
}

func writeJSON(w http.ResponseWriter, status int, value any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(value)
}

func (s *Server) loggingMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		started := time.Now()
		requestID := requestID(r.Header.Get("X-Request-ID"))
		w.Header().Set("X-Request-ID", requestID)
		recorder := &statusRecorder{ResponseWriter: w, status: http.StatusOK}

		next.ServeHTTP(recorder, r)

		s.logger.Info("http_request",
			"request_id", requestID,
			"method", r.Method,
			"path", r.URL.Path,
			"status", recorder.status,
			"duration_ms", time.Since(started).Milliseconds(),
		)
	})
}

type statusRecorder struct {
	http.ResponseWriter
	status int
}

func (r *statusRecorder) WriteHeader(status int) {
	r.status = status
	r.ResponseWriter.WriteHeader(status)
}

func requestID(candidate string) string {
	if safeRequestID.MatchString(candidate) {
		return candidate
	}

	buffer := make([]byte, 16)
	if _, err := rand.Read(buffer); err != nil {
		return "request-id-unavailable"
	}
	return hex.EncodeToString(buffer)
}
