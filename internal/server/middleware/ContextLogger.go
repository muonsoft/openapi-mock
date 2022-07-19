package middleware

import (
	"io"
	"net"
	"net/http"

	"github.com/gorilla/handlers"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
	"github.com/sirupsen/logrus"
)

type ContextLogger struct {
	logger *logrus.Logger
	next   http.Handler
}

func NewContextLogger(logger *logrus.Logger, next http.Handler) *ContextLogger {
	middleware := &ContextLogger{logger: logger}
	middleware.next = handlers.CustomLoggingHandler(io.Discard, next, middleware.logRequest)

	return middleware
}

func (handler *ContextLogger) ServeHTTP(responseWriter http.ResponseWriter, request *http.Request) {
	requestContext := request.Context()

	logger := handler.logger.WithFields(logrus.Fields{
		"requestId": RequestIDFromContext(requestContext),
	})

	loggerContext := logcontext.WithLogger(requestContext, logger)
	contextualRequest := request.WithContext(loggerContext)

	handler.next.ServeHTTP(responseWriter, contextualRequest)
}

func (handler *ContextLogger) logRequest(_ io.Writer, params handlers.LogFormatterParams) {
	requestID := RequestIDFromContext(params.Request.Context())
	host, _, err := net.SplitHostPort(params.Request.RemoteAddr)
	if err != nil {
		host = params.Request.RemoteAddr
	}

	fields := logrus.Fields{
		"requestID":  requestID,
		"proto":      params.Request.Proto,
		"method":     params.Request.Method,
		"userAgent":  params.Request.UserAgent(),
		"referrer":   params.Request.Referer(),
		"host":       host,
		"uri":        params.URL.RequestURI(),
		"statusCode": params.StatusCode,
		"size":       params.Size,
	}

	handler.logger.
		WithTime(params.TimeStamp).
		WithFields(fields).
		Infof("request completed: %s %s", params.Request.Method, params.Request.URL.Path)
}
