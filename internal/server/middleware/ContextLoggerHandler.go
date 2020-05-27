package middleware

import (
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
	"github.com/sirupsen/logrus"
	"net/http"
)

type contextLoggerHandler struct {
	logger      logrus.FieldLogger
	nextHandler http.Handler
}

func ContextLoggerHandler(logger logrus.FieldLogger, nextHandler http.Handler) http.Handler {
	return &contextLoggerHandler{logger, nextHandler}
}

func (handler *contextLoggerHandler) ServeHTTP(responseWriter http.ResponseWriter, request *http.Request) {
	requestContext := request.Context()

	logger := handler.logger.WithFields(logrus.Fields{
		"requestId": RequestIDFromContext(requestContext),
	})

	loggerContext := logcontext.WithLogger(requestContext, logger)
	contextualRequest := request.WithContext(loggerContext)

	handler.nextHandler.ServeHTTP(responseWriter, contextualRequest)
}
