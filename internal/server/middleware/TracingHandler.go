package middleware

import (
	"context"
	"github.com/gofrs/uuid"
	"net/http"
)

type key int

const (
	requestIDKey key = iota
)

type tracingHandler struct {
	nextHandler http.Handler
}

func TracingHandler(nextHandler http.Handler) http.Handler {
	return &tracingHandler{nextHandler}
}

func (handler *tracingHandler) ServeHTTP(responseWriter http.ResponseWriter, request *http.Request) {
	requestID := request.Header.Get("X-Request-Id")

	if requestID == "" {
		requestID = uuid.Must(uuid.NewV4()).String()
	}

	ctx := context.WithValue(request.Context(), requestIDKey, requestID)
	responseWriter.Header().Set("X-Request-Id", requestID)

	handler.nextHandler.ServeHTTP(responseWriter, request.WithContext(ctx))
}

func RequestIDFromContext(ctx context.Context) string {
	id, exists := ctx.Value(requestIDKey).(string)

	if !exists {
		id = uuid.Nil.String()
	}

	return id
}
